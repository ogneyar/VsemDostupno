<?php

namespace app\models;

use Yii;
use app\models\ProductFeature;

/**
 * This is the model class for table "stock_body".
 *
 * @property integer $id
 * @property integer $stock_head_id
 * @property integer $product_id
 * @property string $tare
 * @property double $weight
 * @property string $measurement
 * @property integer $count
 * @property integer $summ
 * @property double $total_summ
 * @property integer $deposit
 * @property string $comment
 * @property integer $product_feature_id
 * @property integer $is_weights
 */
class StockBody extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_body';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stock_head_id', 'product_id', 'weight', 'measurement', 'count', 'summ', 'total_summ', 'deposit', 'product_feature_id'], 'required'],
            [['stock_head_id', 'product_id', 'count', 'deposit', 'is_weights'], 'integer'],
            [['weight', 'total_summ', 'summ'], 'number'],
            [['comment'], 'string'],
            [['tare', 'measurement'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stock_head_id' => 'ID приёмки',
            'product_id' => 'Наименование товара',
            'tare' => 'Тара',    
            'weight' => 'Масса',
            'measurement' => 'Ед. измерения',
            'count' => 'Количество',
            'summ' => 'Цена за единицу',
            'total_summ' => 'Общая сумма',
            'deposit' => 'Зачислять на лицевой счёт',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */


    public function getProvider_stock()
    {
        return $this->hasOne(ProviderStock::className(), ['stock_body_id' => 'id']);
    }



    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getStockHead()
    {
        return $this->hasOne(StockHead::className(), ['id' => 'stock_head_id']);
    }

    public function getProductName()
    {
        return $this->product->name;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductFeature()
    {
        return $this->hasOne(ProductFeature::className(), ['id' => 'product_feature_id']);
    }
    
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $product_feature = ProductFeature::find()->where(['id' => $this->product_feature_id])->one();
            $product_feature->quantity -= $this->provider_stock->reaminder_rent;
            if ($product_feature->quantity < 0) {
                $product_feature->quantity = 0;
            }
            $product_feature->save();
            
            return true;
        } else {
            return false;
        }
    }
}


