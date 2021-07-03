<?php

namespace app\modules\site\models\profile;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * ForgotChangeForm is the model behind the login form.
 */
class ForgotChangeForm extends Model
{
    public $token;
    public $password;
    public $password_repeat;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['token', 'password', 'password_repeat'], 'required'],
            [['token'], 'string', 'max' => 255],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
        ];
    }
}
