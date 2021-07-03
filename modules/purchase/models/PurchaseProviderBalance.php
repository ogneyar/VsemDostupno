<?php

namespace app\modules\purchase\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\Provider;
use app\models\User;

/**
 * This is the model class for table "purchase_provider_balance".
 *
 * @property integer $id
 * @property integer $provider_id
 * @property integer $user_id
 * @property integer $purchase_order_product_id
 * @property string $total
 *
 * @property Provider $provider
 * @property User $user
 * @property PurchaseOrderProduct $purchaseOrderProduct
 */
class PurchaseProviderBalance extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purchase_provider_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id', 'purchase_order_product_id', 'total'], 'required'],
            [['provider_id', 'user_id', 'purchase_order_product_id'], 'integer'],
            [['total'], 'number'],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
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
            'provider_id' => 'Provider ID',
            'user_id' => 'User ID',
            'purchase_order_product_id' => 'Purchase Order Product ID',
            'total' => 'Total',
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
}
