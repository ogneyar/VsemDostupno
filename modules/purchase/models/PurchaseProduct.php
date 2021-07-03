<?php

namespace app\modules\purchase\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\models\ProductFeature;
use app\models\Provider;

use app\modules\purchase\models\PurchaseOrderProduct;

/**
 * This is the model class for table "purchase_product".
 *
 * @property integer $id
 * @property string $created_date
 * @property string $purchase_date
 * @property string $stop_date
 * @property integer $renewal
 * @property string $purchase_total
 * @property integer $is_weights
 * @property string $tare
 * @property double $weight
 * @property string $measurement
 * @property string $summ
 * @property integer $product_feature_id
 * @property integer $provider_id
 * @property string $comment
 * @property integer $send_notification
 * @property string $status
 * @property integer $copy
 *
 * @property ProductFeature $productFeature
 */
class PurchaseProduct extends \yii\db\ActiveRecord
{
    public $purchase_product_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purchase_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_date', 'purchase_date', 'stop_date', 'purchase_total', 'weight', 'measurement', 'summ', 'product_feature_id', 'provider_id', 'status'], 'required'],
            [['created_date', 'purchase_date', 'stop_date'], 'safe'],
            [['renewal', 'is_weights', 'product_feature_id', 'provider_id', 'send_notification', 'copy'], 'integer'],
            [['purchase_total', 'weight', 'summ'], 'number'],
            [['comment'], 'string'],
            [['tare', 'measurement'], 'string', 'max' => 10],
            [['product_feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductFeature::className(), 'targetAttribute' => ['product_feature_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_date' => 'Дата оформления',
            'purchase_date' => 'Дата закупки',
            'stop_date' => 'Дата Стоп заказа',
            'renewal' => 'Renewal',
            'purchase_total' => 'Сумма заказа',
            'is_weights' => 'Is Weights',
            'tare' => 'Тара',
            'weight' => 'Масса/Объём',
            'measurement' => 'Ед. измерения',
            'summ' => 'Сумма за ед./т.',
            'product_feature_id' => 'Наименование товара',
            'provider_id' => 'Provider ID',
            'comment' => 'Комментарий',
            'send_notification' => 'Send Notification',
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
    public function getPurchaseOrderProducts()
    {
        return $this->hasMany(PurchaseOrderProduct::className(), ['purchase_product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }
    
    public function getProductName()
    {
        return $this->productFeature->product->name;
    }
    
    public function getFormattedPurchaseDate()
    {
        return Yii::$app->formatter->asDate($this->purchase_date, 'long');
    }

    public function getFormattedStopDate()
    {
        return Yii::$app->formatter->asDate($this->stop_date, 'long');
    }

    public function getHtmlFormattedPurchaseDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedPurchaseDate);
    }

    public function getHtmlFormattedStopDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedStopDate);
    }
    
    public static function getPurchaseDateByFeature($f_id)
    {
        return self::find()->where(['product_feature_id' => $f_id, 'status' => 'advance'])->andWhere('stop_date >= "' . date('Y-m-d') . '"')->orderBy('purchase_date ASC, stop_date ASC')->limit(1)->all();
    }
    
    public static function getProviderProducts($provider_id)
    {
        $query = self::find()
            ->where(['provider_id' => $provider_id])
            ->andWhere(['<>', 'status', 'abortive'])
            ->orderBy('purchase_date');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);
        
        return $dataProvider;
    }
    
    public static function getProviderProductsGrouped($provider_id)
    {
        $query = new Query;
        $query->select([
                'purchase_product.purchase_date',
                'SUM(purchase_provider_balance.total) as balance_total'
            ])
            ->from('purchase_provider_balance')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_provider_balance.purchase_order_product_id = purchase_order_product.id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_provider_balance.provider_id' => $provider_id])
            ->andWhere(['<>', 'purchase_product.status', 'abortive'])
            ->groupBy('purchase_date')
            ->orderBy('purchase_date');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);
        
        return $dataProvider;
    }
    
    public function getOrderedCount()
    {
        return PurchaseOrderProduct::find()->where(['purchase_product_id' => $this->id])->sum('quantity');
    }
    
    public function getOrderedTotal()
    {
        return number_format($this->summ * $this->orderedCount, 2, '.', '');
    }
    
    public static function getClosestDate($products)
    {
        if ($products) {
            $date = 9999999999;
            foreach ($products as $product) {
                if (isset($product->productFeatures)) {
                    foreach ($product->productFeatures as $feat) {
                        if (isset($feat->purchaseProducts)) {
                            foreach ($feat->purchaseProducts as $purchase) {
                                if (strtotime($purchase->stop_date) >= strtotime(date('Y-m-d')) && $purchase->status == 'advance') {
                                    if ($purchase->purchase_date < $date) {
                                        $date = $purchase->purchase_date;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $date;
        }
        return false;
    }
    
    public static function getClosestDateForProduct($product)
    {
        if ($product) {
            if (isset($product->productFeatures)) {
                $date = 9999999999;
                foreach ($product->productFeatures as $feat) {
                    if (isset($feat->purchaseProducts)) {
                        foreach ($feat->purchaseProducts as $purchase) {
                            if (strtotime($purchase->stop_date) >= strtotime(date('Y-m-d')) && $purchase->status == 'advance') {
                                if ($purchase->purchase_date < $date) {
                                    $date = $purchase->purchase_date;
                                }
                            }
                        }
                    }
                }
                return $date;
            }
            
        }
        return false;
    }
    
    public static function getSortedView($categories)
    {
        foreach ($categories as $k => $cat) {
            if ($cat->isPurchase()) {
                $productsQuery = $cat->getAllProductsQuery()
                    ->andWhere('visibility != 0')
                    ->andWhere('published != 0'); 
                $products = $productsQuery->all();
                $categories[$k]->purchase_date = self::getClosestDate($products);
                if (empty($categories[$k]->purchase_date)) {
                    $categories[$k]->purchase_date = '2038-01-01';
                }
            }
        }
        
        @usort($categories, function($a, $b) {
            if ($a->isPurchase()) {
                if (strtotime($a['purchase_date']) == strtotime($b['purchase_date'])) {
                    return ($a['name'] > $b['name']);
                }
                return (strtotime($a['purchase_date']) > strtotime($b['purchase_date']));
            }
            return ($a['name'] > $b['name']);
        });
        
        return $categories;
    }
    
    public static function getSortedViewItems($categories)
    {
        foreach ($categories as $k => $cat) {
            if ($cat['model']->isPurchase()) {
                $productsQuery = $cat['model']->getAllProductsQuery()
                    ->andWhere('visibility != 0')
                    ->andWhere('published != 0'); 
                $products = $productsQuery->all();
                $categories[$k]['purchase_date'] = self::getClosestDate($products);
                if (empty($categories[$k]['purchase_date'])) {
                    $categories[$k]['purchase_date'] = '2038-01-01';
                }
            }
        }
        
        @usort($categories, function($a, $b) {
            if ($a['model']->isPurchase()) {
                if (strtotime($a['purchase_date']) == strtotime($b['purchase_date'])) {
                    return ($a['content'] > $b['content']);
                }
                return (strtotime($a['purchase_date']) > strtotime($b['purchase_date']));
            }
            return ($a['content'] > $b['content']);
        });
        
        return $categories;
    }
}
