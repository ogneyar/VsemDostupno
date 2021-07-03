<?php

namespace app\models;

use Yii;
use app\modules\purchase\models\PurchaseProduct;

/**
 * This is the model class for table "product_feature".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $volume
 * @property string $measurement
 * @property string $tare
 * @property integer $quantity
 * @property integer $is_weights
 *
 * @property Product $product
 */
class ProductFeature extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $cart_quantity = 1;
    public $purchase_product_id;
    public static function tableName()
    {
        return 'product_feature';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'volume', 'quantity'], 'required'],
            [['product_id', 'is_weights'], 'integer'],
            [['volume', 'quantity'], 'number'],
            [['measurement', 'tare'], 'string', 'max' => 10],
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
            'volume' => 'Масса/Объем',
            'measurement' => 'Ед. измерения',
            'tare' => 'Тара',
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
    public function getProductPrices()
    {
        return $this->hasMany(ProductPrice::className(), ['product_feature_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFundProducts()
    {
        return $this->hasMany(FundProduct::className(), ['product_feature_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductNewPrices()
    {
        return $this->hasMany(ProductNewPrice::className(), ['product_feature_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockBodies()
    {
        return $this->hasMany(StockBody::className(), ['product_feature_id' => 'id']);
    }
    
     /**
     * @return \yii\db\ActiveQuery
     */
    public function getFundCommonPrices()
    {
        return $this->hasMany(FundCommonPrice::className(), ['product_feature_id' => 'id']);
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseProducts()
    {
        return $this->hasMany(PurchaseProduct::className(), ['product_feature_id' => 'id']);
    }


    public static function getFeatureByProduct($product_id)
    {
        $res = self::find()->where(['product_id' => $product_id])->orderBy('id')->limit(1)->all();
        if ($res) {
            return ' (' . $res[0]->tare . ', ' . $res[0]->volume . ' ' . $res[0]->measurement . ')';
        }
    }
    
    public static function getPurchaseTypeProduct($product_id)
    {
        $res = self::find()->where(['product_id' => $product_id])->all();
        if ($res) {
            foreach ($res as $val) {
                if (count($val->purchaseProducts)) {
                    return ' - оформлен как закупка';
                }
            }
            return ' - оформлен со склада';
        }
        return '';
    }
    
    public static function getQuantityByProduct($product_id)
    {
        $res = self::find()->where(['product_id' => $product_id])->orderBy('id')->limit(1)->all();
        if ($res) {
            return $res[0]->quantity;
        }
    }
    
    public function getCalculatedPrice($is_w = true)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->is_weights == 1 ? ($is_w ? $this->productPrices[0]->member_price * $this->volume : $this->productPrices[0]->member_price) : $this->productPrices[0]->member_price;
        }

        return $this->is_weights == 1 ? ($is_w ? $this->productPrices[0]->price * $this->volume : $this->productPrices[0]->price) : $this->productPrices[0]->price;
    }
    
    public function getCalculatedTotalPrice()
    {
        return $this->cart_quantity * $this->calculatedPrice;
    }
    
    public function getFormattedCalculatedTotalPrice()
    {
        return Yii::$app->formatter->asCurrency($this->calculatedTotalPrice, 'RUB');
    }
    
    public function getFormattedCalculatedPrice()
    {
        return Yii::$app->formatter->asCurrency($this->calculatedPrice, 'RUB');
    }
    
    public function getPurchase_price()
    {
        return $this->productPrices[0]->purchase_price;
    }
    
    public function getFeatureName()
    {
        return $this->tare . ', ' . $this->volume . ' ' . $this->measurement;
    }
    
    public static function getFeatureNameById($id)
    {
        $feature = self::findOne($id);
        return (!empty($feature->tare) ? $feature->tare . ', ' : "") . $feature->volume . ' ' . $feature->measurement;
    }
    
    public static function isWeights($id)
    {
        $feature = self::findOne($id);
        return $feature->is_weights == 1 ? true : false;
    }
}
