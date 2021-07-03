<?php

namespace app\modules\purchase\models;

use Yii;
use app\models\Product;
use app\models\ProductFeature;
use app\models\Provider;

/**
 * This is the model class for table "purchase_order_product".
 *
 * @property integer $id
 * @property integer $purchase_order_id
 * @property integer $product_id
 * @property integer $purchase_product_id
 * @property string $name
 * @property string $price
 * @property string $quantity
 * @property string $total
 * @property string $purchase_price
 * @property integer $provider_id
 * @property integer $product_feature_id
 * @property integer $deleted
 * @property integer $deleted_p
 * @property integer $status
 * @property integer $reorder
 *
 * @property PurchaseOrder $purchaseOrder
 * @property ProductFeature $productFeature
 * @property Product $product
 * @property PurchaseProduct $purchaseProduct
 * @property Provider $provider
 */
class PurchaseOrderProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purchase_order_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_order_id', 'purchase_product_id', 'name', 'price', 'quantity', 'total', 'purchase_price'], 'required'],
            [['purchase_order_id', 'product_id', 'purchase_product_id', 'provider_id', 'product_feature_id', 'deleted', 'deleted_p', 'reorder'], 'integer'],
            [['price', 'quantity', 'total', 'purchase_price'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['purchase_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['purchase_order_id' => 'id']],
            [['product_feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductFeature::className(), 'targetAttribute' => ['product_feature_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['purchase_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseProduct::className(), 'targetAttribute' => ['purchase_product_id' => 'id']],
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
            'purchase_order_id' => 'Идентификатор заказа',
            'product_id' => 'Идентификатор товара',
            'purchase_product_id' => 'Purchase Product ID',
            'name' => 'Название',
            'price' => 'Цена',
            'quantity' => 'Количество',
            'total' => 'Стоимость',
            'purchase_price' => 'Закупочная цена',
            'provider_id' => 'Provider ID',
            'product_feature_id' => 'Product Feature ID',
            'deleted' => 'Deleted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(), ['id' => 'purchase_order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductFeature()
    {
        return $this->hasOne(ProductFeature::className(), ['id' => 'product_feature_id']);
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
    public function getPurchaseProduct()
    {
        return $this->hasOne(PurchaseProduct::className(), ['id' => 'purchase_product_id']);
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
    public function getPurchaseFundBalances()
    {
        return $this->hasMany(PurchaseFundBalance::className(), ['purchase_order_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseProviderBalances()
    {
        return $this->hasMany(PurchaseProviderBalance::className(), ['purchase_order_product_id' => 'id']);
    }
    
    public static function getProductTotal($product_id)
    {
        return self::find()
            ->where(['purchase_product_id' => $product_id])
            ->andWhere(['status' => 'advance'])
            ->sum('purchase_price * quantity');
    }
}
