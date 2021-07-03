<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\Product;

/**
 * This is the model class for table "mailing_product".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $for_members
 * @property integer $for_providers
 * @property string $for_candidates
 * @property integer $mailing_category_id
 * @property string $subject
 * @property string $message
 * @property string $sent_date
 *
 * @property MailingCategory $mailingCategory
 * @property Product $product
 */
class MailingProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'mailing_category_id', 'subject', 'message'], 'required'],
            [['product_id', 'for_members', 'for_providers', 'mailing_category_id'], 'integer'],
            [['message'], 'string'],
            [['sent_date'], 'safe'],
            [['for_candidates'], 'string', 'max' => 50],
            [['subject'], 'string', 'max' => 255],
            [['mailing_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => MailingCategory::className(), 'targetAttribute' => ['mailing_category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'product_id' => 'Товар',
            'for_members' => 'Отправка для пользователей',
            'for_providers' => 'Отправка для поставщиков',
            'for_candidates' => 'Отправка для кандидатов',
            'mailing_category_id' => 'Категория рассылки',
            'subject' => 'Тема',
            'message' => 'Сообщение',
            'sent_date' => 'Время отправки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingCategory()
    {
        return $this->hasOne(MailingCategory::className(), ['id' => 'mailing_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
    
    public static function getForSend()
    {
        return self::find()->where('NOW() > sent_date')->all();
    }
}
