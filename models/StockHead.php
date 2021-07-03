<?php

namespace app\models;

use Yii;
use app\models\ProductFeature;

/**
 * This is the model class for table "stock_head".
 *
 * @property integer $id
 * @property string $who
 * @property string $date
 * @property integer $provider_id
 * @property boolean $deleted_by_admin
 * @property boolean $deleted_by_provider
 */
class StockHead extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stock_head';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['who', 'date', 'provider_id'], 'required'],
            [['date'], 'safe'],
            [['provider_id'], 'integer'],
            [['who'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'who' => 'Лицо, ответственное за приём',
            'date' => 'Дата приёмки',
            'provider_id' => 'ФИО или наименование организации поставщика',
        ];
    }

    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }

    public function getStockBody()
    {
        return $this->hasMany(StockBody::className(), ['stock_head_id' => 'id']);
    }

    public function getProviderName()
    {
        return $this->provider->name;
    }
    
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if ($this->stockBody) {
                foreach ($this->stockBody as $body) {
                    $product_feature = ProductFeature::find()->where(['id' => $body->product_feature_id])->one();
                    $product_feature->quantity -= $body->provider_stock->reaminder_rent;
                    if ($product_feature->quantity < 0) {
                        $product_feature->quantity = 0;
                    }
                    $product_feature->save();
                }
            }
            return true;
        } else {
            return false;
        }
    }
}
