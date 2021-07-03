<?php

namespace app\modules\mailing\models;

use Yii;

/**
 * This is the model class for table "mailing_category".
 *
 * @property integer $id
 * @property string $name
 */
class MailingCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'name' => 'Назавние',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingUsers()
    {
        return $this->hasMany(MailingUser::className(), ['mailing_category_id' => 'id']);
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingProducts()
    {
        return $this->hasMany(MailingProduct::className(), ['mailing_category_id' => 'id']);
    }
}
