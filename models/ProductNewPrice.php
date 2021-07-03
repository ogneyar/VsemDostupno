<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_new_price".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $price
 * @property integer $quantity      
 * @property integer $product_feature_id
 * @property Product $product
 * @property date $date
 */
class ProductNewPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_new_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'price', 'quantity', 'date', 'product_feature_id'], 'required'],
            [['product_id', 'quantity'], 'integer'],
            [['price'], 'number'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'price' => 'Цена',
            'quantity' => 'Количество',
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
    public function getProductFeature()
    {
        return $this->hasOne(ProductFeature::className(), ['id' => 'product_feature_id']);
    }
    
    public static function getProducts()
    {
        return self::find()
            ->joinWith('product')
            ->joinWith('productFeature')
            ->where(['product.visibility' => 1, 'product.published' => 1, 'product_feature.quantity' => 0])
            ->groupBy('product_id')
            ->orderBy('date ASC')
            ->all();
    }
}
