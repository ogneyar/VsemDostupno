<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fund_common_price".
 *
 * @property integer $id
 * @property integer $product_feature_id
 * @property string $price
 *
 * @property ProductFeature $productFeature
 */
class FundCommonPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fund_common_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_feature_id', 'price'], 'required'],
            [['product_feature_id'], 'integer'],
            [['price'], 'number'],
            [['product_feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductFeature::className(), 'targetAttribute' => ['product_feature_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'product_feature_id' => 'Характеристика товара',
            'price' => 'Цена',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductFeature()
    {
        return $this->hasOne(ProductFeature::className(), ['id' => 'product_feature_id']);
    }
}
