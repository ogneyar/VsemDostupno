<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * MemberForm is the model behind the create/update form.
 */
class MemberForm extends Model
{
    public $isNewRecord = true;
    public $id;
    public $user_id;
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
    public $recommender_info;
    public $number;
    public $recommender_id;
    public $partner;
    public $become_provider;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['recommender_id', 'partner', 'email', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department'], 'required'],
            [['partner', 'disabled', 'recommender_id'], 'integer'],
            [['phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
            [['email'], 'validateEmail'],
            [['number'], 'validateNumber'],
            [['become_provider'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'recommender_id' => 'Номер рекомендателя',
            'disabled' => 'Отключен',
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
            'number' => 'Номер',
            'become_provider'=>'Стать поставщиком?',
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
