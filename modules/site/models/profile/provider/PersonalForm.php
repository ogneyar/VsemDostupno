<?php

namespace app\modules\site\models\profile\provider;

use Yii;
use yii\base\Model;
use app\models\Provider;

/**
 * PersonalForm is the model behind the login form.
 */
class PersonalForm extends Model
{
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
    public $field_of_activity;
    public $offered_goods;
    public $legal_address;
    public $snils;
    public $ogrn;
    public $site;
    public $description;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department', 'field_of_activity', 'offered_goods', 'legal_address', 'snils', 'ogrn'], 'required'],
            [['user'], 'integer'],
            [['name', 'phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['password', 'password_repeat'], 'string', 'min' => 8, 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills'], 'safe'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Не совпадает с паролем.'],
            [['name'], 'validateName'],
            [['field_of_activity', 'offered_goods', 'description'], 'string'],
            [['name', 'legal_address'], 'string', 'max' => 255],
            [['snils'], 'string', 'max' => 11],
            [['ogrn'], 'string', 'max' => 13],
            [['site'], 'string', 'max' => 100],
        ];
    }

    public function validateName($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $provider = Provider::findOne(['name' => $this->name]);

            if ($provider && $provider->user_id != $this->user) {
                $this->addError($attribute, 'Указанное «Название» уже зарегистрировано.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
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
            'field_of_activity' => 'Сфера деятельности организации по ОКВЕД',
            'offered_goods' => 'Наименования предлагаемых товаров',
            'legal_address' => 'Юридический адрес',
            'snils' => 'СНИЛС',
            'ogrn' => 'ОГРН',
            'site' => 'Сайт компании',
            'description' => 'Описание предложений',
        ];
    }
}
