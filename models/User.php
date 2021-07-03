<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $role
 * @property integer $disabled
 * @property string $email
 * @property string $phone
 * @property string $ext_phones
 * @property string $firstname
 * @property string $lastname
 * @property string $patronymic
 * @property string $created_at
 * @property string $created_ip
 * @property string $logged_in_at
 * @property string $logged_in_ip
 * @property string $password
 * @property string $auth_key
 * @property string $access_token
 * @property string $birthdate
 * @property string $citizen
 * @property string $registration
 * @property string $residence
 * @property string $passport
 * @property string $passport_date
 * @property string $passport_department
 * @property string $itn
 * @property string $skills
 * @property string $recommender_info
 * @property integer $number
 * @property integer $recommender_id
 * @property integer $request
 *
 * @property string $shortName
 * @property string $fullName
 * @property Forgot $forgot
 * @property Register $register
 * @property Member $member
 * @property Partner $partner
 * @property Provider $provider
 * @property string $createdAt
 * @property string $loggedInAt
 * @property Order[] $orders
 * @property Account[] $accounts
 * @property Account $deposit
 * @property Account $bonus
 * @property Account $group
 * @property Account $subscription
 * @property Account $storage
 * @property Account $fraternity
 * @property AccountLog[] $toAccountLogs
 * @property AccountLog[] $fromAccountLogs
 * @property Service[] $services
 * @property User $recommender
 * @property string $roleName
 * @property User[] $recommendedUsers
 * @property SubscriberPayment[] $subscriberPayments
 */
class User extends \yii\db\ActiveRecord
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';
    const ROLE_PARTNER = 'partner';
    const ROLE_PROVIDER = 'provider';
    const ROLE_SUPERADMIN = 'superadmin';

    const SUBSCRIBER_MONTHS_INTERVAL = 3;

    protected $old_password;
    public $password_repeat;
    public $re_captcha;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role', 'phone', 'firstname', 'lastname', 'patronymic', 'created_ip', 'auth_key', 'access_token', 'password', 'email'], 'required'],
            [['citizen', 'registration', 'passport', 'passport_department'], 'required', 'when' => function ($model) {return $model->role != self::ROLE_ADMIN && $model->role != self::ROLE_PROVIDER && $model->role != self::ROLE_SUPERADMIN;}],
            [['re_captcha'], 'required', 'except' => ['admin_creation', 'user_login']],
            [['role', 'skills'], 'string'],
            [['disabled', 'number', 'recommender_id', 'request'], 'integer'],
            [['created_at', 'logged_in_at', 'birthdate', 'passport_date'], 'safe'],
            [['phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'created_ip', 'logged_in_ip', 'password', 'auth_key', 'access_token', 'registration', 'residence', 'passport_department', 'recommender_info'], 'string', 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['recommender_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['recommender_id' => 'id']],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'role' => 'Роль',
            'disabled' => 'Отключен',
            'email' => 'Емайл',
            'phone' => 'Телефон',
            'ext_phones' => 'Дополнительные телефоны',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'created_at' => 'Дата и время создания',
            'createdAt' => 'Дата и время создания',
            'created_ip' => 'IP-адрес создания',
            'logged_in_at' => 'Дата и время входа',
            'loggedInAt' => 'Дата и время входа',
            'logged_in_ip' => 'IP-адрес входа',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
            'auth_key' => 'Ключ авторизации',
            'access_token' => 'Токен доступа',
            'birthdate' => 'Дата рождения',
            'citizen' => 'Гражданство',
            'registration' => 'Адрес регистрации',
            'residence' => 'Адрес фактического пребывания',
            'passport' => 'Серия и номер паспорта',
            'passport_date' => 'Дата выдачи паспорта',
            'passport_department' => 'Кем выдан паспорт',
            'itn' => 'ИНН',
            'skills' => 'Профессиональные навыки',
            'recommender_info' => 'Информация о рекомендателе',
            'number' => 'Номер',
            'recommender_id' => 'Идентификатор рекомендателя',
            'fullName' => 'ФИО',
            'shortName' => 'ФИО',
            're_captcha' => 'Проверка',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if ($this->member) {
                $this->member->delete();
            } elseif ($this->partner) {
                if ($this->partner->members) {
                    return false;
                }
                $this->partner->delete();
            } elseif ($this->provider) {
                $this->provider->delete();
            }

            foreach ($this->services as $service) {
                $service->delete();
            }

            foreach ($this->accounts as $account) {
                $account->delete();
            }

            foreach ($this->subscriberPayments as $subscriberPayment) {
                $subscriberPayment->delete();
            }

            foreach ($this->recommendedUsers as $user) {
                $user->recommender_id = null;
                $user->save();
            }

            foreach ($this->orders as $order) {
                $order->user_id = NULL;
                $order->save();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForgot()
    {
        return $this->hasOne(Forgot::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegister()
    {
        return $this->hasOne(Register::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Account::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToAccountLogs()
    {
        return $this->hasMany(AccountLog::className(), ['to_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromAccountLogs()
    {
        return $this->hasMany(AccountLog::className(), ['from_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommender()
    {
        return $this->hasOne(User::className(), ['id' => 'recommender_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommendedUsers()
    {
        return $this->hasMany(User::className(), ['recommender_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriberPayments()
    {
        return $this->hasMany(SubscriberPayment::className(), ['user_id' => 'id']);
    }

    public function getShortName()
    {
        return sprintf(
            '%s %s. %s.',
            $this->lastname,
            mb_substr($this->firstname, 0, 1, Yii::$app->charset),
            mb_substr($this->patronymic, 0, 1, Yii::$app->charset)
        );
    }

    public function getFullName()
    {
        return implode(' ', [$this->lastname, $this->firstname, $this->patronymic]);
    }
    
    public function getRespectedName()
    {
        return implode(' ', [$this->firstname, $this->patronymic]);
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->isNewRecord || $this->old_password != $this->password) {
                $this->updateAuthData();
            }

            $this->phone = preg_replace('/\D+/', '', $this->phone);
            if ($this->phone) {
                $this->phone = '+' . $this->phone;
            }

            return true;
        }

        return false;
    }

    public function afterFind()
    {
        $this->old_password = $this->password;

        return parent::afterFind();
    }

    public function updateAuthData()
    {
        $this->password = self::generatePassword($this);
        if (Yii::$app->controller->module->id !== 'admin') {
            $this->password_repeat = self::generatePassword($this, $this->password_repeat);
        }
        
        $this->auth_key = self::generateAuthKey($this);
        $this->access_token = self::generateAccessToken($this);
    }

    public static function generatePassword($user, $pass = "")
    {
        $pass = empty($pass) ? $user->password : $pass;
        return sha1(Yii::$app->params['secret'] . $user->email . $pass);
    }

    public static function generateAuthKey($user)
    {
        return sha1(Yii::$app->params['secret'] . $user->email . $user->password . rand());
    }

    public static function generateAccessToken($user)
    {
        return sha1(Yii::$app->params['secret'] . $user->email . $user->password . rand());
    }

    public static function getBuyerRoles()
    {
        return [
            self::ROLE_MEMBER,
            self::ROLE_PARTNER,
            self::ROLE_PROVIDER
        ];
    }

    public function getCreatedAt()
    {
        return preg_replace('/[\s0:-]/', '', $this->created_at) ? $this->created_at : '';
    }

    public function getLoggedInAt()
    {
        return preg_replace('/[\s0:-]/', '', $this->logged_in_at) ? $this->logged_in_at : '';
    }

    public function getAccount($type)
    {
        $account = Account::find()
            ->where('user_id = :user_id AND type = :type', [':user_id' => $this->id, ':type' => $type])
            ->one();

        return $account;
    }

    public function getDeposit()
    {
        return $this->getAccount(Account::TYPE_DEPOSIT);
    }

    public function getBonus()
    {
        return $this->getAccount(Account::TYPE_BONUS);
    }

    public function getGroup()
    {
        return $this->getAccount(Account::TYPE_GROUP);
    }

    public function getSubscription()
    {
        return $this->getAccount(Account::TYPE_SUBSCRIPTION);
    }

    public function getStorage()
    {
        return $this->getAccount(Account::TYPE_STORAGE);
    }

    public function getFraternity()
    {
        return $this->getAccount(Account::TYPE_FRATERNITY);
    }

    public function getRoleName()
    {
        $roleNames = [
            self::ROLE_ADMIN => 'администратор',
            self::ROLE_MEMBER => 'участник',
            self::ROLE_PARTNER => 'партнер',
            self::ROLE_PROVIDER => 'поставщик',
            self::ROLE_SUPERADMIN => 'суперадминистратор'
        ];

        return isset($roleNames[$this->role]) ? $roleNames[$this->role] : 'нет роли';
    }
    
    public static function existsEntityRequest()
    {
        $user = self::find()->where(['request' => 1])->one();
        return $user ? 1 : 0;
    }
}
