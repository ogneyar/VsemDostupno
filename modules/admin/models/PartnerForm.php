<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Partner;

/**
 * PartnerForm is the model behind the create/update form.
 */
class PartnerForm extends Model
{
    public $isNewRecord = true;
    public $id;
    public $user_id;
    public $name;
    public $disabled;
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
    public $city;
    public $number;
    public $recommender_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['recommender_id', 'name', 'city', 'email', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department'], 'required'],
            [['city', 'disabled', 'recommender_id'], 'integer'],
            [['name', 'phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
            [['email'], 'validateEmail'],
            [['name'], 'validateName'],
            [['number'], 'validateNumber'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'recommender_id' => 'Номер рекомендателя',
            'name' => 'Название',
            'disabled' => 'Отключен',
            'city' => 'Город',
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
            'number' => 'Номер',
        ];
    }

    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors() && $this->isNewRecord) {
            $user = User::findOne(['email' => $this->email]);

            if ($user) {
                $this->addError($attribute, 'Указанный «Емайл» уже зарегистрирован.');
            }
        }
    }

    public function validateName($attribute, $params)
    {
        if (!$this->hasErrors() && $this->isNewRecord) {
            $partner = Partner::findOne(['city_id' => $this->city, 'name' => $this->name]);

            if ($partner) {
                $this->addError($attribute, 'Указанное «Название» уже зарегистрировано.');
            }
        }
    }

    public function validateNumber($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['number' => $this->number]);

            if ($user && ($this->isNewRecord || $user->id != $this->user_id)) {
                $this->addError($attribute, 'Указанный «Номер» уже существует.');
            }
        }
    }

    public function getRecommender()
    {
        return User::findOne($this->recommender_id);
    }
}
