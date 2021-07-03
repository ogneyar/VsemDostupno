<?php

namespace app\models;

use Yii;
use app\models\ProductPrice;
use app\models\FundProduct;
use app\models\FundCommonPrice;

/**
 * This is the model class for table "fund".
 *
 * @property integer $id
 * @property string $name
 * @property string $percent
 * @property string $deduction_total
 */
class Fund extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'percent'], 'required'],
            [['percent', 'deduction_total'], 'number'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'name' => 'Фонды',
            'percent' => 'Процент',
            'deduction_total' => 'Сумма отчислений',
        ];
    }
     
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFundProducts()
    {
        return $this->hasMany(FundProduct::className(), ['fund_id' => 'id']);
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->recalculatePrice();
    }
    
    public function afterDelete()
    {
        $this->recalculatePrice();
    }
    
    public function recalculatePrice()
    {
        $products = ProductPrice::find()->all();
        if ($products) {
            foreach ($products as $product) {
                $total_percent = self::find()->sum('percent');
                $fund_product = FundProduct::find()->where(['product_feature_id' => $product->product_feature_id])->all();
                if ($fund_product) {
                    foreach ($fund_product as $f_product) {
                        $fund_common = self::find()->where(['id' => $f_product->fund_id])->one();
                        $total_percent -= $fund_common->percent;
                        $total_percent += $f_product->percent;
                    }
                }
                $percent_member = $product->purchase_price / 100 * $total_percent;
                $product->member_price = round($product->purchase_price + $percent_member, 2);
                $percent_all = $product->member_price / 100 * 40;
                $common_price = FundCommonPrice::find()->where(['product_feature_id' => $product->product_feature_id])->one();
                $product->price = $common_price ? $common_price->price : round($product->member_price + $percent_all, 2);
                $product->save();
            }
        }
    }
    
    public static function calculateMemberPrice($price, $feature_id)
    {
        $total_percent = self::find()->sum('percent');
        $fund_product = FundProduct::find()->where(['product_feature_id' => $feature_id])->all();
        if ($fund_product) {
            foreach ($fund_product as $f_product) {
                $fund_common = self::find()->where(['id' => $f_product->fund_id])->one();
                $total_percent -= $fund_common->percent;
                $total_percent += $f_product->percent;
            }
        }
        $percent_member = $price / 100 * $total_percent;
        return round($price + $percent_member, 2);
    }
    
    public static function calculateAllPrice($price, $feature_id)
    {
        $percent_all = $price / 100 * 40;
        $common_price = FundCommonPrice::find()->where(['product_feature_id' => $feature_id])->one();
        return $common_price ? $common_price->price : round($price + $percent_all, 2);
    }
    
    public static function setDeductionForOrder($feature_id, $price, $qnt)
    {
        $funds_common = self::find()->all();
        if ($funds_common) {
            foreach ($funds_common as $common) {
                $fund_product = FundProduct::find()->where(['product_feature_id' => $feature_id, 'fund_id' => $common->id])->one();
                if ($fund_product) {
                    $deduction = $price / 100 * $fund_product->percent;
                } else {
                    $deduction = $price / 100 * $common->percent;
                }
                $common->deduction_total += round($deduction * $qnt, 2);
                $common->save();
            }
        }
    }
}
