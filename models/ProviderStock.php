<?php

namespace app\models;

use Yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use app\models\Provider;

/**
 * This is the model class for table "stock_body".
 *
 * @property integer $id
 * @property integer $stock_body_id
 * @property integer $total_rent
 * @property integer $total_sum
 * @property integer $reaminder_rent
 * @property integer $summ_reminder
 */
class ProviderStock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider_stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stock_body_id', 'total_rent', 'total_sum', 'reaminder_rent', 'summ_reminder', 'summ_on_deposit'], 'required'],
            [['stock_body_id', 'total_rent', 'total_sum', 'reaminder_rent', 'summ_reminder', 'summ_on_deposit'], 'number'],
            
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stock_body_id' => 'ID поставки',
            'total_rent' => 'Сдано общее кол-во',
            'total_sum' => 'На общую сумму',
            'reaminder_rent' => 'Количество на остатке',
            'summ_reminder' => 'Остаток на общую сумму',
            'summ_on_deposit' => 'Сумма на лицевом счёте',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStock_body()
    {
        return $this->hasOne(StockBody::className(),['id'=>'stock_body_id']);
    }

    public function getUnitContibution()
    {
       return $this->hasOne(UnitContibution::className(),['provider_stock_id'=>'id']);
    }
    
    public static function getDepositsByProvider($provider_id, $is_admin = false)
    {
        if ($is_admin) {
            $add_where = ['stock_head.deleted_by_admin' => 0];
        } else {
            $add_where = ['stock_head.deleted_by_provider' => 0];
        }
        $query = new Query;
        $query->select([
                'stock_head.provider_id',
                'stock_head.id',
                'stock_head.date',
                'SUM(provider_stock.total_sum) AS total_sum',
                'SUM(provider_stock.summ_reminder) AS summ_reminder',
                'SUM(provider_stock.summ_on_deposit) AS dep_summ'
            ])
            ->from('provider_stock')
            ->join('LEFT JOIN', 'stock_body', 'provider_stock.stock_body_id=stock_body.id')
            ->join('LEFT JOIN', 'stock_head', 'stock_body.stock_head_id=stock_head.id')
            ->where(['stock_head.provider_id' => $provider_id, 'stock_body.deposit' => 1])
            ->andWhere($add_where)
            ->groupBy('stock_head.id')
            ->orderBy('stock_head.date DESC');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        
        return $dataProvider;
    }
    
    public static function isSolded($body_id)
    {
        $res = self::find()->where(['stock_body_id' => $body_id])->one();
        if ($res) {
            if ($res->total_rent != $res->reaminder_rent) {
                return true;
            }
        }
        return false;
    }
    
    public static function getCurrentStock($product_id, $provider_id)
    {
        return ProviderStock::findBySql(
            'SELECT ps.* FROM provider_stock as ps 
                INNER JOIN stock_body as body ON ps.stock_body_id=body.id 
                INNER JOIN stock_head as head ON body.stock_head_id=head.id 
            WHERE body.product_feature_id =' . $product_id . ' 
                AND head.provider_id =' . $provider_id . '
                AND ps.reaminder_rent > 0
            ORDER BY head.date ASC'
        )->one();
    }
    
    public static function getCurrentStockReturn($product_id, $provider_id)
    {
        return ProviderStock::findBySql(
            'SELECT ps.* FROM provider_stock as ps 
                INNER JOIN stock_body as body ON ps.stock_body_id=body.id 
                INNER JOIN stock_head as head ON body.stock_head_id=head.id 
            WHERE body.product_feature_id =' . $product_id . ' 
                AND head.provider_id =' . $provider_id . '
                AND ps.reaminder_rent < total_rent
            ORDER BY head.date DESC'
        )->one();
    }
    
    public static function getCurrentStockSum($provider_id)
    {
        return self::find()
            ->joinWith('stock_body')
            ->joinWith('stock_body.stockHead')
            ->where(['stock_head.provider_id' => $provider_id, 'stock_body.deposit' => 1])
            ->andWhere(['>', 'summ_on_deposit', 0])
            ->orderBy('stock_head.date ASC')
            ->one();
    }
    
    public static function setStockSum($user_id, $amount)
    {
        $amount = abs($amount);
        $provider = Provider::findOne(['user_id' => $user_id]);
        $stock = self::getCurrentStockSum($provider->id);
        
        if ($stock) {
            if ($stock->summ_on_deposit >= $amount) {
                $stock->summ_on_deposit -= $amount;
                $stock->save();
            } else {
                $rest = $amount - $stock->summ_on_deposit;
                $stock->summ_on_deposit = 0;
                $stock->save();
                
                while ($rest > 0) {
                    $stock = self::getCurrentStockSum($provider->id);
                    if ($stock) {
                        if ($stock->summ_on_deposit >= $rest) {
                            $stock->summ_on_deposit -= $rest;
                            $stock->save();
                            $rest = 0;
                        } else {
                            $rest -= $stock->summ_on_deposit;
                            $stock->summ_on_deposit = 0;
                            $stock->save();
                        }
                    } else {
                        $rest = 0;
                    }
                }
            }
        }
        
        return true;
    }
}
