<?php

namespace app\modules\admin\models;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\Provider;

/**
 * ProviderForm is the model behind the create/update form.
 */
class ProviderForm extends Model
{
    public $isNewRecord = true;
    public $id;
    public $user_id;
    public $partner;
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
    public $number;
    public $categoryIds;
    public $categories;
    public $recommender_id;
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
            [['recommender_id', 'partner', 'name', 'email', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department', 'field_of_activity', 'legal_address', 'snils', 'ogrn'], 'required'],
            [['disabled', 'recommender_id'], 'integer'],
            [['name', 'phone', 'ext_phones', 'firstname', 'lastname', 'patronymic', 'registration', 'residence', 'passport_department'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['citizen'], 'string', 'max' => 50],
            [['passport', 'itn'], 'string', 'max' => 30],
            [['skills', 'categoryIds', 'categories'], 'safe'],
            [['email'], 'validateEmail'],
            [['name'], 'validateName'],
            [['number'], 'validateNumber'],
            [['field_of_activity', 'description'], 'string'],
            [['name', 'legal_address'], 'string', 'max' => 255],
            [['snils'], 'string', 'max' => 11],
            [['ogrn'], 'string', 'max' => 13],
            [['site'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'recommender_id' => 'Номер рекомендателя',
            'partner' => 'Партнёр',
            'name' => 'Название',
            'disabled' => 'Отключен',
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
            'field_of_activity' => 'Сфера деятельности организации по ОКВЕД',
            'offered_goods' => 'Наименования предлагаемых товаров',
            'legal_address' => 'Юридический адрес',
            'snils' => 'СНИЛС',
            'ogrn' => 'ОГРН',
            'site' => 'Сайт компании',
            'description' => 'Описание предложений',
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
            $provider = Provider::findOne(['name' => $this->name]);

            if ($provider) {
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
