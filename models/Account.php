<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use app\models\Email;
use app\models\User;
use app\models\ProviderStock;


require_once __DIR__ . '/../modules/bots/utils/account/getPay.php';
require_once __DIR__ . '/../modules/bots/utils/account/getRole.php';


/**
 * This is the model class for table "account".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $type
 * @property string $total
 *
 * @property User $user
 * @property Member $member
 * @property AccountLog[] $accountLogs
 * @property string $typeName
 */
class Account extends \yii\db\ActiveRecord
{
    const TYPE_DEPOSIT = 'deposit'; // расчётный счёт // у суперадмина это СЧЁТ ПО
    const TYPE_BONUS = 'bonus'; // инвестиционный счёт // у суперадмина это ФОНД СОДРУЖЕСТВА
    const TYPE_SUBSCRIPTION = 'subscription'; // членский взнос (долг, хранится в положительном значении) // у суперадмина это сумма взымаемых ЧЛЕНСКИХ ВЗНОСОВ
    const TYPE_STORAGE = 'storage'; // партнёрский счёт  // у суперадмина это ЧЛЕНСКИЕ ВЗНОСЫ (общая сумма)
    // 347 - id суперадмина
    const TYPE_RECOMENDER = 'recomender'; // рекомендательский взнос // походу можно удалять
    const TYPE_GROUP = 'group'; // расчётный счёт группы // походу можно удалять
    const TYPE_GROUP_FEE = 'group_fee'; // членские взносы группы // походу можно удалять
    const TYPE_FRATERNITY = 'fraternity'; // фонд содружества // походу можно удалять

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'total'], 'required'],
            [['user_id'], 'integer'],
            [['type'], 'string'],
            [['total'], 'number'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'user_id' => 'Идентификатор пользователя',
            'type' => 'Тип счета',
            'total' => 'Сумма',
            'typeName' => 'Название типа счета',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountLogs()
    {
        return $this->hasMany(AccountLog::className(), ['account_id' => 'id']);
    }

    public function getTypeName()
    {
        $typeNames = [
            self::TYPE_DEPOSIT => 'расчётный счёт',
            self::TYPE_BONUS => 'инвестиционный счёт', // старое название - 'бонус'
            self::TYPE_STORAGE => 'партнёрский счёт', // старое название - 'складской сбор',
            
            self::TYPE_SUBSCRIPTION => 'членский взнос',
            self::TYPE_RECOMENDER => 'рекомендательский сбор',

            self::TYPE_GROUP => 'расчётный счёт группы', // старое название - 'групповой бонус'
            self::TYPE_GROUP_FEE => 'членские взносы группы',  // старое название - 'групповой взнос'

            self::TYPE_FRATERNITY => 'фонд содружества',
        ];

        return isset($typeNames[$this->type]) ? $typeNames[$this->type] : 'неизвестный';
    }

    public static function transfer($account, $from, $to, $amount, $message, $sendEmail = true)
    {
        if (!(($from && $account->user->id == $from->id) || ($to && $account->user->id == $to->id))) {
            return false;
        }

        if ($amount == 0 || ($amount < 0 && bccomp(abs($amount), $account->total, 2) == 1)) {
            return false;
        }

        if (is_a(Yii::$app,'yii\web\Application')) {
            // if ($account->type != Account::TYPE_DEPOSIT) {
            //     throw new Exception('Нет доступа к счету!');
            // }
            if (!(Yii::$app->user->identity->role == User::ROLE_ADMIN || Yii::$app->user->identity->role == User::ROLE_SUPERADMIN)) {
                // throw new Exception('Нет доступа к счету!');
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!AccountLog::record($account, $from, $to, $amount, $message)) {
                throw new Exception('Ошибка сохранения журанала счета!');
            }
            $account->total += $amount;
            if (!$account->save()) {
                throw new Exception('Ошибка сохранения счета!');
            }
            
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            return false;
        }

        if ($sendEmail) {            
            // if ($account->user->tg_id) Email::tg_send('account-log', $account->user->tg_id, [
            //     'typeName' => $account->typeName,
            //     'message' => $message,
            //     'amount' => $amount,
            //     'total' => $account->total,
            // ]);
            
            if ($account->user->tg_id) Email::tg_send('new-account-log', $account->user->tg_id, [
                'role' => getRole($account->user),
                'number' => $account->user->number,
                'message' => $message,
                'amount' => $amount,
                'total' => $account->user->deposit->total,
                'invest' => $account->user->bonus->total,
                'pay' => getPay($account->user),
            ]);
        }

        return true;
    }

    public static function swap($from, $to, $amount, $message, $sendEmail = false)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$from && !$to) {
                throw new Exception('Ошибка указания счетов!');
            }

            if ($from && !Account::transfer($from, $from->user, $to ? $to->user : null, -$amount, $message, false)) {
                throw new Exception('Ошибка сохранения счета Источника!');
            }

            if ($from && $from->type == Account::TYPE_BONUS && $message == "Перевод пая на Расчётный счет") { 
                $message = "Перевод пая с Инвестиционного счёта";
            }

            if ($to && !Account::transfer($to, $from ? $from->user : null, $to->user, $amount, $message, false)) {
                throw new Exception('Ошибка сохранения счета Приемника!');
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            return false;
        }

        if ($sendEmail) {
            if ($from) {
                // if ($from->user->tg_id) Email::tg_send('account-log', $from->user->tg_id, [
                //     'typeName' => $from->typeName,
                //     'message' => $message,
                //     'amount' => -$amount,
                //     'total' => $from->total,
                // ]);
                
                if ($from->user->tg_id) Email::tg_send('new-account-log', $from->user->tg_id, [
                    'role' => getRole($from->user),
                    'number' => $from->user->number,
                    'message' => $message,
                    'amount' => -$amount,
                    'total' => $from->user->deposit->total,
                    'invest' => $from->user->bonus->total,
                    'pay' => getPay($from->user),
                ]);
            }

            if ($to) {
                // if ($to->user->tg_id) Email::tg_send('account-log', $to->user->tg_id, [
                //     'typeName' => $to->typeName,
                //     'message' => $message,
                //     'amount' => $amount,
                //     'total' => $to->total,
                // ]);

                if ($to->user->tg_id) Email::tg_send('new-account-log', $to->user->tg_id, [
                    'role' => getRole($to->user),
                    'number' => $to->user->number,
                    'message' => $message,
                    'amount' => $amount,
                    'total' => $to->user->deposit->total,
                    'invest' => $to->user->bonus->total,
                    'pay' => getPay($to->user),
                ]);
            }
        }

        return true;
    }
}
