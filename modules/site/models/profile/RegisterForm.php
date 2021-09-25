<?php

namespace app\modules\site\models\profile;

use Yii;
use yii\base\Model;
use app\models\User;
use himiklab\yii2\recaptcha\ReCaptchaValidator;

/**
 * RegisterForm is the model behind the login form.
 */
class RegisterForm extends Model
{
    public $recommender_id;
    public $recommender_info;
    public $partner;
    public $email;
    public $phone;
    public $ext_phones;
    public $firstname;
    public $lastname;
    public $patronymic;
    public $birthdate;
    public $citizen;
    public $registration;
    public $residence;
    public $passport;
    public $passport_date;
    public $passport_department;
    public $itn;
    public $skills;
    public $password;
    public $password_repeat;
    public $re_captcha;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['recommender_id', 'partner', 'email', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department', 'password', 'password_repeat', 're_captcha'], 'required'],
            [['partner'], 'integer'],
            [['phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department', 'recommender_info'], 'string', 'max' => 255],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
            [['email'], 'email'],
            [['email'], 'validateEmail'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
            [['re_captcha'], ReCaptchaValidator::className()],
        ];
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['email' => $this->email]);

            if ($user) {
                $this->addError($attribute, 'Указанный «Емайл» уже зарегистрирован.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'recommender_id' => 'Рекомендатель',
            'partner' => 'Партнер',
            'email' => 'Емайл',
            'phone' => 'Телефон',
            'ext_phones' => 'Дополнительные телефоны',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
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
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
            're_captcha' => 'Проверка',
        ];
    }

    function afterValidate()
    {
        parent::afterValidate();
    }

    
    public function getRecommender()
    {
        return User::findOne($this->recommender_id);
    }

}
