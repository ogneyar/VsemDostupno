<?php

namespace app\modules\site\models\profile\partner;

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

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department'], 'required'],
            [['disabled'], 'integer'],
            [['phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'disabled' => 'Отключен',
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
        ];
    }
}
