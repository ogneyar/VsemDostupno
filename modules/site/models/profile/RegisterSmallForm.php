<?php

namespace app\modules\site\models\profile;

use Yii;
use yii\base\Model;
use app\models\User;
use himiklab\yii2\recaptcha\ReCaptchaValidator;

/**
 * RegisterSmallForm is the model behind the login form.
 */
class RegisterSmallForm extends Model
{
    public $partner;
    public $phone;
    public $firstname;
    public $patronymic;
    public $password;
    public $password_repeat;
    public $re_captcha;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['partner',  'phone', 'firstname', 'patronymic', 'password', 'password_repeat', 're_captcha'], 'required'],
            [['partner'], 'integer'],
            [['phone', 'firstname', 'patronymic'], 'string', 'max' => 255],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
            [['re_captcha'], ReCaptchaValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'partner' => 'Партнер',
            'phone' => 'Телефон',
            'firstname' => 'Имя',
            'patronymic' => 'Отчество',
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
