<?php

namespace app\models;

use Yii;
use yii\db\Query;
use app\models\Order;

/**
 * This is the model class for table "order_has_product".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $product_id
 * @property string $name
 * @property string $price
 * @property string $quantity
 * @property string $total
 * @property string $purchase_timestamp
 * @property string $order_timestamp
 * @property integer $purchase
 * @property string $purchase_price
 * @property string $storage_price
 * @property string $invite_price
 * @property string $fraternity_price
 * @property string $group_price
 * @property integer $provider_id
 * @property Product $product
 * @property Order $order
 * @property string $formattedPrice
 * @property string $formattedTotal
 * @property string $purchaseDate
 * @property string $orderDate
 * @property string $formattedPurchaseDate
 * @property string $formattedOrderDate
 * @property string $htmlFormattedPurchaseDate
 * @property string $htmlFormattedOrderDate
 */
class OrderHasProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_has_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'name', 'price', 'quantity', 'total', 'purchase_price', 'storage_price', 'invite_price', 'fraternity_price', 'group_price', 'product_feature_id'], 'required'],
            [['order_id', 'product_id', 'purchase','provider_id'], 'integer'],
            [['purchase_timestamp', 'order_timestamp'], 'safe'],
            [['price', 'quantity', 'total', 'purchase_price', 'storage_price', 'invite_price', 'fraternity_price', 'group_price'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
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
            'order_id' => 'Идентификатор заказа',
            'product_id' => 'Идентификатор товара',
            'name' => 'Название',
            'price' => 'Цена',
            'formattedPrice' => 'Цена',
            'quantity' => 'Количество',
            'total' => 'Стоимость',
            'formattedTotal' => 'Стоимость',
            'purchase_timestamp' => 'Дата и время закупки',
            'order_timestamp' => 'Дата и время последних заказов',
            'purchase' => 'Закупка',
            'purchase_price' => 'Закупочная цена',
            'storage_price' => 'Складской сбор',
            'invite_price' => 'Отчисление рекомендателю',
            'fraternity_price' => 'Отчисление в фонд Содружества',
            'group_price' => 'Отчисление в фонд Группы',
            'provider_id' => 'Идентификатор поставщика',
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
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductFeature()
    {
        return $this->hasOne(ProductFeature::className(), ['id' => 'product_feature_id']);
    }


    public function getFormattedPrice()
    {
        return Yii::$app->formatter->asCurrency($this->price, 'RUB');
    }

    public function getFormattedTotal()
    {
        return Yii::$app->formatter->asCurrency($this->total, 'RUB');
    }

    public function getPurchaseDate()
    {
        if (strtotime($this->purchase_timestamp) > 0) {
            return mb_substr($this->purchase_timestamp, 0, 10, Yii::$app->charset);
        }

        return '';
    }

    public function getFormattedPurchaseDate()
    {
        if (strtotime($this->purchase_timestamp) > 0) {
            return Yii::$app->formatter->asDate($this->purchase_timestamp, 'long');
        }

        return '';
    }

    public function getHtmlFormattedPurchaseDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedPurchaseDate);
    }

    public function setPurchaseDate($value)
    {
        $this->purchase_timestamp = $value ? $value : date('Y-m-d H:i:s');
    }

    public function getOrderDate()
    {
        if (strtotime($this->order_timestamp) > 0) {
            return mb_substr($this->order_timestamp, 0, 10, Yii::$app->charset);
        }

        return '';
    }

    public function getFormattedOrderDate()
    {
        if (strtotime($this->order_timestamp) > 0) {
            return Yii::$app->formatter->asDate($this->order_timestamp, 'long');
        }

        return '';
    }

    public function getHtmlFormattedOrderDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedPurchaseDate);
    }

    public function setOrderDate($value)
    {
        $this->order_timestamp = $value ? $value : date('Y-m-d H:i:s');
    }
    
    public static function getSumTotalByOrder($order_id)
    {
        return self::find()->where(['order_id' => $order_id])->sum('total');
    }
}
