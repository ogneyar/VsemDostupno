<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "provider_reg_data_tmp".
 *
 * @property integer $id
 * @property string $ip
 * @property integer $step
 * @property string $phone
 * @property string $firstname
 * @property string $lastname
 * @property string $patronymic
 * @property string $birthdate
 * @property string $citizen
 * @property string $registration
 * @property string $passport
 * @property string $passport_date
 * @property string $passport_department
 * @property string $ext_phones
 * @property string $name
 * @property string $field_of_activity
 * @property string $legal_address
 * @property string $snils
 * @property string $ogrn
 * @property string $site
 * @property string $itn
 * @property string $category
 */
class ProviderRegData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider_reg_data_tmp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'step', 'phone', 'firstname', 'lastname', 'patronymic', 'birthdate', 'citizen', 'registration', 'passport', 'passport_date', 'passport_department'], 'required', 'on' => 'reg_step_1'],
            [['ip', 'step', 'name', 'field_of_activity', 'itn', 'snils', 'ogrn', 'legal_address', 'category'], 'required', 'on' => 'reg_step_2'],
            [['step'], 'integer'],
            [['field_of_activity', 'category'], 'string'],
            [['ip', 'phone', 'firstname', 'lastname', 'patronymic', 'registration', 'passport_department', 'ext_phones', 'name', 'legal_address'], 'string', 'max' => 255],
            [['citizen'], 'string', 'max' => 50],
            [['site'], 'string', 'max' => 100],
            [['site'], 'url'],
            [['birthdate'], 'validateBirthdate'],
            [['passport_date'], 'validatePassportDate'],
            [['passport'], 'validatePassport'],
            [['itn'], 'validateItn'],
            [['snils'], 'validateSnils'],
            [['ogrn'], 'validateOgrn'],
            [['category'], 'validateCategory'],
        ];
    }
    
    public function validateBirthdate($attribute, $params)
    {
        $adultAge = strtotime(date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 16)));
        if (strtotime($this->birthdate) > $adultAge) {
            $this->addError($attribute, 'Вам должно быть не менее 16 лет');
        }
    }
    
    public function validatePassportDate($attribute, $params)
    {
        if (strtotime($this->birthdate) > strtotime($this->passport_date) || strtotime($this->passport_date) > time()) {
            $this->addError($attribute, 'Проверьте дату выдачи паспорта');
        }
    }
    
    public function validatePassport($attribute, $params)
    {
        if (!preg_match('/^[0-9]{10}$/', $this->passport)) {
            $this->addError($attribute, 'Должно содержать 10 цифр без пробелов');
        }
    }
    
    public function validateItn($attribute, $params)
    {
        if (!preg_match('/^[0-9]{10}$/', $this->itn) && !preg_match('/^[0-9]{12}$/', $this->itn)) {
            $this->addError($attribute, 'Должно содержать 10 или 12 цифр без пробелов');
        }
    }
    
    public function validateSnils($attribute, $params)
    {
        if (!preg_match('/^[0-9]{11}$/', $this->snils)) {
            $this->addError($attribute, 'Должно содержать 11 цифр без пробелов и тире');
        }
    }
    
    public function validateOgrn($attribute, $params)
    {
        if (!preg_match('/^[0-9]{13}$/', $this->ogrn)) {
            $this->addError($attribute, 'Должно содержать 13 цифр без пробелов');
        }
    }
    
    public function validateCategory($attribute, $params)
    {
        $val = json_decode($this->category);
        if (count($val) == 0) {
            $this->addError($attribute, 'Выберите как минимум одну категорию');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'ip' => 'IP-адрес создания',
            'step' => 'Шаг регистрации',
            'phone' => 'Телефон',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'birthdate' => 'Дата рождения',
            'citizen' => 'Гражданство',
            'registration' => 'Адрес регистрации',
            'passport' => 'Серия и номер паспорта',
            'passport_date' => 'Дата выдачи паспорта',
            'passport_department' => 'Кем выдан паспорт',
            'ext_phones' => 'Дополнительные телефоны',
            'name' => 'Наименование организации',
            'field_of_activity' => 'Сфера деятельности (по ОКВЕД)',
            'legal_address' => 'Юридический адрес',
            'snils' => 'СНИЛС',
            'ogrn' => 'ОГРН',
            'site' => 'Сайт компании',
            'itn' => 'ИНН',
            'category' => 'Категории',
        ];
    }
    
    public static function getStepByIp($ip)
    {
        $res = self::find()->where(['ip' => $ip])->one();
        if ($res) {
            return $res;
        }
        return false;
    }
}
