<?php

namespace app\models;

use Yii;
use app\models\Fund;
use app\models\FundCommonPrice;
use app\models\ProductPrice;

/**
 * This is the model class for table "fund_product".
 *
 * @property integer $id
 * @property integer $product_feature_id
 * @property integer $fund_id
 * @property string $percent
 *
 * @property ProductFeature $productFeature
 * @property Fund $fund
 */
class FundProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fund_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_feature_id', 'fund_id', 'percent'], 'required'],
            [['product_feature_id', 'fund_id'], 'integer'],
            [['percent'], 'number'],
            [['product_feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductFeature::className(), 'targetAttribute' => ['product_feature_id' => 'id']],
            [['fund_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fund::className(), 'targetAttribute' => ['fund_id' => 'id']],
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
            'fund_id' => 'Фонд',
            'percent' => 'Процент',
        ];
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
    public function getFund()
    {
        return $this->hasOne(Fund::className(), ['id' => 'fund_id']);
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->recalculatePriceByFeature($this->product_feature_id);
    }
    
    public function getTotalPercentByFeature($feature_id)
    {
        $total = 0;
        $funds_common = Fund::find()->all();
        foreach ($funds_common as $common) {
            $fund_product = self::find()->where(['product_feature_id' => $feature_id, 'fund_id' => $common->id])->one();
            if ($fund_product) {
                $total += $fund_product->percent;
            } else {
                $total += $common->percent;
            }
        }
        return $total;
    }
    
    public function recalculatePriceByFeature($feature_id)
    {
        $total_percent = $this->getTotalPercentByFeature($feature_id);
        $product_price = ProductPrice::find()->where(['product_feature_id' => $feature_id])->one();
        $percent_member = $product_price->purchase_price / 100 * $total_percent;
        $product_price->member_price = round($product_price->purchase_price + $percent_member, 2);
        $percent_all = $product_price->member_price / 100 * 40;
        $common_price = FundCommonPrice::find()->where(['product_feature_id' => $feature_id])->one();
        $product_price->price = $common_price ? $common_price->price : round($product_price->member_price + $percent_all, 2);
        $product_price->save();
    }
}
