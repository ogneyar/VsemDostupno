<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "provider_has_product".
 *
 * @property integer $id
 * @property integer $provider_id
 * @property integer $product_id
 *
 * @property Product $product
 * @property Provider $provider
 */
class ProviderHasProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider_has_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id', 'product_id'], 'required'],
            [['provider_id', 'product_id'], 'integer'],
            [['provider_id', 'product_id'], 'unique', 'targetAttribute' => ['provider_id', 'product_id'], 'message' => 'The combination of Идентификатор поставщика and Идентификатор товара has already been taken.'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'provider_id' => 'Идентификатор поставщика',
            'product_id' => 'Идентификатор товара',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }
}
