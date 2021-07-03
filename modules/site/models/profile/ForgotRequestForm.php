<?php

namespace app\modules\site\models\profile;

use Yii;
use yii\base\Model;
use app\models\User;
use himiklab\yii2\recaptcha\ReCaptchaValidator;

/**
 * ForgotRequestForm is the model behind the login form.
 */
class ForgotRequestForm extends Model
{
    public $email;
    public $re_captcha;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['email', 're_captcha'], 'required'],
            [['email'], 'email'],
            [['email'], 'validateEmail'],
            [['re_captcha'], ReCaptchaValidator::className()],
        ];
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['email' => $this->email, 'disabled' => 0]);

            if (!$user) {
                $this->addError($attribute, 'Указанный «Емайл» не зарегистрирован или заблокирован.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Емайл',
            're_captcha' => 'Проверка',
        ];
    }
}
