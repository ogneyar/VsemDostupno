<?php
namespace app\models;
use Yii;

/**
 * This is model class for Manufacturer table
 * @property integer $id
 * @property string $field_of_activity
 * @property string $offered_goods
 * @property string $company_name
 * @property string $fio
 * @property integer $itn
 * @property string $insurance_certificate
 * @property string $ogrn
 * @property string $email
 * @property string $company_site
 * @property string $phone
 * @property string $additional_phone
 * @property string $company_description
 * @property string $adress
**/
class Manufacturer extends \yii\db\ActiveRecord{
    public static function tableName()
    {
        return 'manufacturer';
        
    }

    public function rules()
    {
     return[
         [['itn'],'integer'],
         [['field_of_activity','offered_goods','company_name','fio','itn','insurance_certificate','ogrn','company_site','phone','additional_phone','company_description','adress'],'required'],
         [['email'],'email'],
     ];
    }

    public function attributeLabels()
    {
        return [
            'id'=>'Идентификатор',
            'field_of_activity'=>'Сфера деятельности',
            'offered_goods'=>'Предлагаемые продукты',
            'company_name'=>'Название компании',
            'fio'=>'ФИО контактного лица',
            'insurance_certificate'=>'Страховое свидетельство',
            'ogrn'=>'ОГРН',
            'email'=>'Электронная почта',
            'company_site'=>'Сайт компании',
            'phone'=>'Телефон',
            'additional_phone'=>'Дополнительный телефон',
            'company_description'=>'Описание',
            'adress'=>'Юридический адрес',
            'itn'=>'ИНН',
        ];
    }
}
?>