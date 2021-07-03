<?php

namespace app\models;

use Yii;
use app\models\Fund;

/**
 * This is the model class for table "product_price".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $product_feature_id
 * @property string $purchase_price
 * @property string $member_price
 * @property string $price
 *
 * @property Product $product
 * @property ProductFeature $productFeature
 */
class ProductPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'product_feature_id', 'purchase_price', 'member_price', 'price'], 'required'],
            [['product_id', 'product_feature_id'], 'integer'],
            [['purchase_price', 'member_price', 'price'], 'number'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'product_id' => 'Товар',
            'product_feature_id' => 'Характеристика товара',
            'purchase_price' => 'Закупочная цена',
            'member_price' => 'Цена для участников',
            'price' => 'Цена для всех',
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
    
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (empty($this->member_price) && empty($this->price)) {
                $this->member_price = Fund::calculateMemberPrice($this->purchase_price, $this->product_feature_id);
                $this->price = Fund::calculateAllPrice($this->member_price, $this->product_feature_id);
            }
        }
        return true;
    }
    
    public static function getMemberPriceByProduct($product_id)
    {
        $res = self::find()->where(['product_id' => $product_id])->orderBy('id')->limit(1)->all();
        if ($res) {
            return $res[0]->member_price;
        }
    }
    
    public static function getAllPriceByProduct($product_id)
    {
        $res = self::find()->where(['product_id' => $product_id])->orderBy('product_feature_id')->limit(1)->all();
        if ($res) {
            return $res[0]->price;
        }
    }
}
