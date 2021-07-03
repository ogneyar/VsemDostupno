<?php

namespace app\modules\purchase\models;

use Yii;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\models\Fund;
use app\models\FundProduct;
use app\models\User;

/**
 * This is the model class for table "purchase_fund_balance".
 *
 * @property integer $id
 * @property integer $fund_id
 * @property integer $user_id
 * @property integer $purchase_order_product_id
 * @property string $total
 * @property integer $paid
 *
 * @property Fund $fund
 * @property User $user
 * @property PurchaseOrderProduct $purchaseOrderProduct
 */
class PurchaseFundBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purchase_fund_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fund_id', 'purchase_order_product_id', 'total'], 'required'],
            [['fund_id', 'user_id', 'purchase_order_product_id', 'paid'], 'integer'],
            [['total'], 'number'],
            [['fund_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fund::className(), 'targetAttribute' => ['fund_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['purchase_order_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrderProduct::className(), 'targetAttribute' => ['purchase_order_product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fund_id' => 'Fund ID',
            'user_id' => 'User ID',
            'purchase_order_product_id' => 'Purchase Order Product ID',
            'total' => 'Total',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFund()
    {
        return $this->hasOne(Fund::className(), ['id' => 'fund_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderProduct()
    {
        return $this->hasOne(PurchaseOrderProduct::className(), ['id' => 'purchase_order_product_id']);
    }
    
    public static function setDeductionForOrder($order_id, $user_id)
    {
        $funds_common = Fund::find()->all();
        $order = PurchaseOrderProduct::findOne($order_id);
        if ($funds_common) {
            foreach ($funds_common as $common) {
                $fund_product = FundProduct::find()->where(['product_feature_id' => $order->product_feature_id, 'fund_id' => $common->id])->one();
                if ($fund_product) {
                    $deduction = $order->purchase_price / 100 * $fund_product->percent;
                } else {
                    $deduction = $order->purchase_price / 100 * $common->percent;
                }
                $purchase_fund = new PurchaseFundBalance();
                $purchase_fund->fund_id = $common->id;
                $purchase_fund->user_id = $user_id;
                $purchase_fund->purchase_order_product_id = $order_id;
                $purchase_fund->total = round($deduction * $order->quantity, 2);
                $purchase_fund->save();
            }
        }
    }
}
