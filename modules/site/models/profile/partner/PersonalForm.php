<?php

namespace app\modules\site\models\profile\partner;

use Yii;
use yii\base\Model;
use app\models\Partner;

/**
 * PersonalForm is the model behind the login form.
 */
class PersonalForm extends Model
{
    public $city;
    public $user;
    public $name;
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

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['city', 'user', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department'], 'required'],
            [['city', 'user'], 'integer'],
            [['name', 'phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
            [['name'], 'validateName'],
        ];
    }

    public function validateName($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $partner = Partner::findOne(['city_id' => $this->city, 'name' => $this->name]);

            if ($partner && $partner->user_id != $this->user) {
                $this->addError($attribute, 'Указанное «Название» уже зарегистрировано.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'city' => 'Город',
            'name' => 'Название',
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
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
        ];
    }
}
