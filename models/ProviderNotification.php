<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "provider_notification".
 *
 * @property integer $id
 * @property string $sent_at
 * @property string $order_date
 * @property integer $provider_id
 * @property integer $product_id
 *
 * @property Provider $provider
 * @property Product $product
 */
class ProviderNotification extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider_notification';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sent_at', 'order_date'], 'safe'],
            [['provider_id', 'product_id'], 'required'],
            [['provider_id', 'product_id'], 'integer'],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sent_at' => 'Sent At',
            'order_date' => 'Order Date',
            'provider_id' => 'Provider ID',
            'product_id' => 'Product ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
