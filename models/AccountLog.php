<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "account_log".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $from_user_id
 * @property integer $to_user_id
 * @property integer $account_id
 * @property string $message
 * @property string $amount
 * @property string $from_firstname
 * @property string $from_lastname
 * @property string $from_patronymic
 * @property string $to_firstname
 * @property string $to_lastname
 * @property string $to_patronymic
 *
 * @property Account $account
 * @property User $fromUser
 * @property User $toUser
 * @property string $fromUserFullName
 * @property string $toUserFullName
 */
class AccountLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'account_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['account_id', 'message', 'amount'], 'required'],
            [['from_user_id', 'to_user_id', 'account_id'], 'integer'],
            [['message'], 'string'],
            [['amount'], 'number'],
            [['from_firstname', 'from_lastname', 'from_patronymic', 'to_firstname', 'to_lastname', 'to_patronymic'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'created_at' => 'Дата операции',
            'from_user_id' => 'Идентификатор пользователя отправителя',
            'to_user_id' => 'Идентификатор пользователя получателя',
            'account_id' => 'Идентификатор счета',
            'message' => 'Сообщение',
            'amount' => 'Сумма',
            'fromUserFullName' => 'От кого',
            'toUserFullName' => 'Кому',
            'from_firstname' => 'Имя отправителя',
            'from_lastname' => 'Фамилия отправителя',
            'from_patronymic' => 'Отчество отправителя',
            'to_firstname' => 'Имя получателя',
            'to_lastname' => 'Фамилия получателя',
            'to_patronymic' => 'Отчество получателя',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    public function getFromUserFullName()
    {
        return implode(' ', [$this->from_lastname, $this->from_firstname, $this->from_patronymic]);
    }

    public function getToUserFullName()
    {
        $ret = (strpos($this->message, 'Произведён обмен паями') === false) ?
            (!empty($this->to_lastname) ? implode(' ', [$this->to_lastname, $this->to_firstname, $this->to_patronymic]) : 'В Потребительское общество') :
            implode(' ', [$this->to_lastname, $this->to_firstname, $this->to_patronymic]) . ' (Обмен между участниками)';
        return $ret;
    }

    public static function record($account, $from, $to, $amount, $message)
    {
        $accountLog = new AccountLog();

        if ($from) {
            $accountLog->from_user_id = $from->id;
            $accountLog->from_firstname = $from->firstname;
            $accountLog->from_lastname = $from->lastname;
            $accountLog->from_patronymic = $from->patronymic;
        }
        if ($to) {
            $accountLog->to_user_id = $to->id;
            $accountLog->to_firstname = $to->firstname;
            $accountLog->to_lastname = $to->lastname;
            $accountLog->to_patronymic = $to->patronymic;
        }
        $accountLog->account_id = $account->id;
        $accountLog->amount = $amount;
        $accountLog->message = $message;

        return $accountLog->save();
    }
}
