<?php

namespace app\modules\purchase\models;

use Yii;
use yii\db\Query;
use yii\data\SqlDataProvider;
use yii\data\ActiveDataProvider;
use app\models\City;
use app\models\Partner;
use app\models\User;
use app\models\Order;
use app\models\Account;
use app\models\Fund;

/**
 * This is the model class for table "purchase_order".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $city_id
 * @property integer $partner_id
 * @property integer $user_id
 * @property string $role
 * @property string $city_name
 * @property string $partner_name
 * @property string $email
 * @property string $phone
 * @property string $firstname
 * @property string $lastname
 * @property string $patronymic
 * @property string $address
 * @property string $total
 * @property string $comment
 * @property string $paid_total
 * @property integer $hide
 * @property integer $order_id
 * @property string $order_number
 * @property string $order_number_copy
 * @property string $status
 * @property integer $reorder
 *
 * @property City $city
 * @property Partner $partner
 * @property User $user
 * @property PurchaseOrderProduct[] $purchaseOrderProducts
 */
class PurchaseOrder extends \yii\db\ActiveRecord
{
    public $formattedTotal;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purchase_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['city_id', 'partner_id', 'user_id', 'hide', 'order_id', 'reorder'], 'integer'],
            [['role', 'address', 'comment', 'status'], 'string'],
            [['city_name', 'email', 'phone', 'firstname', 'lastname', 'patronymic'], 'required'],
            [['total', 'paid_total', 'total'], 'number'],
            [['city_name', 'partner_name', 'email', 'phone', 'firstname', 'lastname', 'patronymic'], 'string', 'max' => 255],
            [['order_number', 'order_number_copy'], 'string', 'max' => 20],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'id']],
            [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Partner::className(), 'targetAttribute' => ['partner_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'city_id' => 'Идентификатор города',
            'partner_id' => 'Идентификатор партнера',
            'user_id' => 'Идентификатор пользователя',
            'role' => 'Роль',
            'city_name' => 'Название города',
            'partner_name' => 'Название партнера',
            'email' => 'Емайл',
            'phone' => 'Телефон',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'address' => 'Адрес доставки',
            'total' => 'Стоимость',
            'comment' => 'Комментарий',
            'paid_total' => 'Оплаченная стоимость',
            'hide' => 'Скрыть из истории',
            'order_id' => 'Order ID',
            'order_number' => 'Order Number',
            'status' => 'Status',
            'formattedTotal' => 'Стоимость',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
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
    public function getPurchaseOrderProducts()
    {
        return $this->hasMany(PurchaseOrderProduct::className(), ['purchase_order_id' => 'id']);
    }
    
    public function getFullName()
    {
        return implode(' ', [$this->lastname, $this->firstname, $this->patronymic]);
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->order_number = 'ПР/' . (new \DateTime($this->created_at))->format('d.m') . '-' . $this->id;
            $this->status = 'advance';
            $this->save();
        }
    }
    
    public static function getPurchaseDates($deleted = 0)
    {
        $whereD = $deleted == -1 ? '1' : 'deleted = ' . $deleted;
        $query = new Query;
        $query->select([
                'purchase_date'
            ])
            ->from('purchase_product')
            ->join('RIGHT JOIN', 'purchase_order_product', 'purchase_product.id=purchase_order_product.purchase_product_id')
            ->where($whereD)
            ->groupBy('purchase_date')
            ->orderBy('purchase_date DESC');
            
        $command = $query->createCommand();
        
        return $command->queryAll();
    }
    
    public static function getPurchaseDatesByPartner($partner_id, $deleted = 0)
    {
        $partner = Partner::findOne($partner_id);
        $whereD = $deleted == -1 ? '1' : 'deleted = ' . $deleted;
        $query = new Query;
        $query->select([
                'purchase_date'
            ])
            ->from('purchase_product')
            ->join('RIGHT JOIN', 'purchase_order_product', 'purchase_product.id=purchase_order_product.purchase_product_id')
            ->join('LEFT JOIN', 'purchase_order', 'purchase_order.id = purchase_order_product.purchase_order_id')
            ->where($whereD)
            ->andWhere('purchase_order.partner_id = ' . $partner_id . ' OR purchase_order.user_id = ' . $partner->user_id)
            ->andWhere(['deleted_p' => 0])
            ->groupBy('purchase_date')
            ->orderBy('purchase_date ASC');
            
        $command = $query->createCommand();
        
        return $command->queryAll();
    }
    
    public static function getPurchaseDatesByUser($user_id)
    {
        $query = new Query;
        $query->select([
                'purchase_date'
            ])
            ->from('purchase_product')
            ->join('RIGHT JOIN', 'purchase_order_product', 'purchase_product.id=purchase_order_product.purchase_product_id')
            ->join('LEFT JOIN', 'purchase_order', 'purchase_order.id = purchase_order_product.purchase_order_id')
            ->where(['purchase_order.user_id' => $user_id])
            ->groupBy('purchase_date')
            ->orderBy('purchase_date ASC');
            
        $command = $query->createCommand();
        
        return $command->queryAll();
    }
    
    public static function getProvidersOrder($date, $deleted = 0)
    {
        $count = 0;
        $whereD = $deleted == -1 ? '1' : 'pop.deleted = ' . $deleted;
        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT pr.id,
                            pop.id AS ohp_id, 
                            po.partner_id AS pid, 
                            po.partner_name,
                            pop.quantity, 
                            pop.name AS product_name,
                            pop.total,
                            p.id AS provider_id,
                            p.name AS provider_name,
                            pop.price,
                            pop.product_feature_id,
                            COUNT(pop.product_feature_id) AS row_cnt,
                            SUM(pop.quantity) AS total_qnt,
                            SUM(pop.total) AS total_price,
                            CONCAT(pf.tare, ", ", pf.volume, " ", pf.measurement) AS product_feature_name
                        FROM `purchase_order` po
                        LEFT JOIN `purchase_order_product` pop ON po.id = pop.purchase_order_id
                        LEFT JOIN `provider` p ON pop.provider_id = p.id
                        LEFT JOIN `product` pr ON pop.product_id = pr.id
                        LEFT JOIN `product_feature` pf ON pop.product_feature_id = pf.id
                        LEFT JOIN `purchase_product` pp ON pp.id = pop.purchase_product_id
                        WHERE `pp`.purchase_date = "' . $date . '"
                            AND ' . $whereD . '
                        GROUP BY product_feature_id',
            //'totalCount' => $count,
            'pagination' => [
                'pageSize' => '100',
            ],
        ]);
        
        return $dataProvider;
    }
    
    public static function getProvidersOrderByPartner($partner_id, $date, $deleted = 0)
    {
        $partner = Partner::findOne($partner_id);
        $count = 0;
        $whereD = $deleted == -1 ? '1' : 'pop.deleted = ' . $deleted;
        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT pr.id,
                            pop.id AS ohp_id, 
                            po.partner_id AS pid, 
                            po.partner_name,
                            pop.quantity, 
                            pop.name AS product_name,
                            pop.total,
                            p.id AS provider_id,
                            p.name AS provider_name,
                            pop.price,
                            pop.product_feature_id,
                            COUNT(pop.product_feature_id) AS row_cnt,
                            SUM(pop.quantity) AS total_qnt,
                            SUM(pop.total) AS total_price,
                            CONCAT(pf.tare, ", ", pf.volume, " ", pf.measurement) AS product_feature_name
                        FROM `purchase_order` po
                        LEFT JOIN `purchase_order_product` pop ON po.id = pop.purchase_order_id
                        LEFT JOIN `provider` p ON pop.provider_id = p.id
                        LEFT JOIN `product` pr ON pop.product_id = pr.id
                        LEFT JOIN `product_feature` pf ON pop.product_feature_id = pf.id
                        LEFT JOIN `purchase_product` pp ON pp.id = pop.purchase_product_id
                        WHERE `pp`.purchase_date = "' . $date . '"
                            AND (po.partner_id = ' . $partner_id . '
                            OR po.user_id = ' . $partner->user_id . ')
                            AND pop.deleted_p = 0
                            AND ' . $whereD . '
                        GROUP BY product_feature_id',
            //'totalCount' => $count,
            'pagination' => [
                'pageSize' => '20',
            ],
        ]);
        
        return $dataProvider;
    }
    
    public static function getOrderByProduct($product, $date)
    {
        $query = new Query;
        $query->select([
                'IF (purchase_order.partner_id IS NULL, partner.id, purchase_order.partner_id) AS p_id',
                'IF (purchase_order.partner_name IS NULL, partner.name, purchase_order.partner_name) AS p_name',
                'SUM(purchase_order_product.quantity) AS quantity',
                'SUM(purchase_order_product.total) AS total'
            ])
            ->from('purchase_order')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_order.id=purchase_order_product.purchase_order_id')
            ->join('LEFT JOIN', 'partner', 'purchase_order.user_id=partner.user_id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_order_product.product_feature_id' => $product])
            ->andWhere(['purchase_product.purchase_date' => $date])
            ->groupBy('p_name');
        
        $command = $query->createCommand();
        return $command->queryAll();
    }
    
    public static function getOrderByProductByPartner($product, $date, $partner_id)
    {
        $partner = Partner::findOne($partner_id);
        $query = new Query;
        $query->select([
                'IF (purchase_order.partner_id IS NULL, partner.id, purchase_order.partner_id) AS p_id',
                'IF (purchase_order.partner_name IS NULL, partner.name, purchase_order.partner_name) AS p_name',
                'SUM(purchase_order_product.quantity) AS quantity',
                'SUM(purchase_order_product.total) AS total'
            ])
            ->from('purchase_order')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_order.id=purchase_order_product.purchase_order_id')
            ->join('LEFT JOIN', 'partner', 'purchase_order.user_id=partner.user_id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_order_product.product_feature_id' => $product])
            ->andWhere(['purchase_product.purchase_date' => $date])
            ->andWhere('purchase_order.partner_id = ' . $partner_id . ' OR purchase_order.user_id = ' . $partner->user_id)
            ->groupBy('p_name');
        
        $command = $query->createCommand();
        return $command->queryAll();
    }
    
    public static function getProviderOrderDetails($product_id, $date, $partner_id)
    {
        $query = new Query;
        $query->select([
                'purchase_order.order_number',
                'purchase_order.order_id',
                'CONCAT(purchase_order.lastname, " ", purchase_order.firstname, " ", purchase_order.patronymic) AS fio',
                'purchase_order_product.quantity',
                'purchase_order_product.price',
                'purchase_order_product.total',
                'purchase_order_product.product_feature_id',
                'purchase_order_product.name',
                'IF (purchase_order.partner_id IS NULL, partner.id, purchase_order.partner_id) AS p_id'
            ])
            ->from('purchase_order')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_order.id=purchase_order_product.purchase_order_id')
            ->join('LEFT JOIN', 'partner', 'purchase_order.user_id=partner.user_id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_order_product.product_feature_id' => $product_id])
            ->andWhere(['purchase_product.purchase_date' => $date])
            ->having(['p_id' => $partner_id]);
        
        $command = $query->createCommand();
        return $command->queryAll();
    }
    
    public static function getDetalization($date, $hide = 0)
    {
        $query = self::find();
        $query->joinWith('purchaseOrderProducts');
        $query->joinWith('purchaseOrderProducts.purchaseProduct');
        $query->where(['purchase_product.purchase_date' => $date]);
        $query->andWhere(['hide' => $hide]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pageSize' => '100',
            ],
        ]);
        
        return $dataProvider;
    }
    
    public static function getDetalizationByPartner($partner_id, $date, $hide = 0)
    {
        $partner = Partner::findOne($partner_id);
        $query = self::find();
        $query->joinWith('purchaseOrderProducts');
        $query->joinWith('purchaseOrderProducts.purchaseProduct');
        $query->where(['purchase_product.purchase_date' => $date]);
        $query->andWhere('partner_id = ' . $partner_id . ' OR user_id = ' . $partner->user_id);
        $query->andWhere(['hide' => $hide]);
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);
        
        return $dataProvider;
    }
    
    public static function getDetalizationByUser($user_id, $date)
    {
        $query = self::find();
        $query->joinWith('purchaseOrderProducts');
        $query->joinWith('purchaseOrderProducts.purchaseProduct');
        $query->where(['purchase_product.purchase_date' => $date]);
        $query->andWhere(['user_id' => $user_id]);
        $query->orderBy('purchase_date');
            
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
        ]);
        
        return $dataProvider;
    }
    
    public function getHtmlEmailFormattedInformation()
    {
        return Yii::$app->view->renderFile('@app/modules/purchase/views/default/order-email-information.php', [
            'model' => $this,
        ]);
    }
    
    public function getHtmlMemberEmailFormattedInformation($date)
    {
        return Yii::$app->view->renderFile('@app/modules/purchase/views/default/order-member-email-information.php', [
            'model' => $this,
            'date' => $date,
        ]);
    }
    
    public static function setOrderStatus($order_id)
    {
        $order = self::findOne($order_id);
        $products_adv = PurchaseOrderProduct::find()->where(['purchase_order_id' => $order_id, 'status' => 'advance'])->all();
        $products_held = PurchaseOrderProduct::find()->where(['purchase_order_id' => $order_id, 'status' => 'held'])->all();
        $products_ab = PurchaseOrderProduct::find()->where(['purchase_order_id' => $order_id, 'status' => 'abortive'])->all();
        
        if ($products_adv && $products_held && !$products_ab) {
            $order->status = 'part_held';
            if (empty($order->order_id)) {
                $order_order_id = Order::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                $purchase_order_id = self::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                if ($order_order_id > $purchase_order_id) {
                    $order->order_id = $order_order_id + 1;
                } else if ($order_order_id < $purchase_order_id) {
                    $order->order_id = $purchase_order_id + 1;
                } else {
                    $order->order_id = 1;
                }
            }
        }
        
        if ($products_adv && !$products_held && $products_ab) {
            $order->status = 'part_abortive';
        }
        
        if (!$products_adv && $products_held && !$products_ab) {
            $order->status = 'held';
            if (empty($order->order_id)) {
                $order_order_id = Order::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                $purchase_order_id = self::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                if ($order_order_id > $purchase_order_id) {
                    $order->order_id = $order_order_id + 1;
                } else if ($order_order_id < $purchase_order_id) {
                    $order->order_id = $purchase_order_id + 1;
                } else {
                    $order->order_id = 1;
                }
            }
            $order->order_number_copy = $order->order_number;
            $order->order_number = null;
        }
        
        if (!$products_adv && !$products_held && $products_ab) {
            $order->status = 'abortive';
            //$order->order_number = null;
        }
        
        if (!$products_adv && $products_held && $products_ab) {
            $order->status = 'completed';
            if (empty($order->order_id)) {
                $order_order_id = Order::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                $purchase_order_id = self::find()->where('YEAR(created_at) = "' . date('Y') . '"')->max('order_id');
                if ($order_order_id > $purchase_order_id) {
                    $order->order_id = $order_order_id + 1;
                } else if ($order_order_id < $purchase_order_id) {
                    $order->order_id = $purchase_order_id + 1;
                } else {
                    $order->order_id = 1;
                }
            }
            $order->order_number_copy = $order->order_number;
            $order->order_number = null;
        }
        
        $order->save();
    }
    
    public static function getOrderDetailsByProviderPartner($date, $provider_id, $partner_id)
    {
        $query = new Query;
        $query->select([
                'IF (purchase_order.partner_id IS NULL, partner.id, purchase_order.partner_id) AS p_id',
                'purchase_order_product.name AS product_name',
                'purchase_order_product.product_id',
                'purchase_order_product.product_feature_id AS product_feature',
                'CONCAT(product_feature.tare, ", ", product_feature.volume, " ", product_feature.measurement) AS product_feature_name',
                'SUM(purchase_order_product.quantity) AS quantity',
                'SUM(purchase_order_product.total) AS total'
            ])
            ->from('purchase_order')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_order.id=purchase_order_product.purchase_order_id')
            ->join('LEFT JOIN', 'partner', 'purchase_order.user_id=partner.user_id')
            ->join('LEFT JOIN', 'product', 'purchase_order_product.product_id=product.id')
            ->join('LEFT JOIN', 'product_feature', 'purchase_order_product.product_feature_id=product_feature.id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_order_product.provider_id' => $provider_id])
            ->andWhere(['purchase_product.purchase_date' => $date])
            ->andWhere(['product.auto_send' => '1'])
            ->groupBy('product_feature, p_id')
            ->having(['p_id' => $partner_id]);
            
        $command = $query->createCommand();
        return $command->queryAll();
    }
    
    public static function getPartnerIdByProvider($date, $provider_id)
    {
        $query = new Query;
        $query->select([
                'DISTINCT(IF (purchase_order.partner_id IS NULL, partner.id, purchase_order.partner_id)) AS partner_id',
            ])
            ->from('purchase_order')
            ->join('LEFT JOIN', 'purchase_order_product', 'purchase_order.id=purchase_order_product.purchase_order_id')
            ->join('LEFT JOIN', 'partner', 'purchase_order.user_id=partner.user_id')
            ->join('LEFT JOIN', 'purchase_product', 'purchase_product.id = purchase_order_product.purchase_product_id')
            ->where(['purchase_order_product.provider_id' => $provider_id])
            ->andWhere(['purchase_product.purchase_date' => $date]);
        
        $command = $query->createCommand();
        return $command->queryAll();
    }
    
    public function getTextStatus()
    {
        switch ($this->status) {
            case 'held':
                $ret = "<span style='color: green;'>Состоявшаяся</span>";
            break;
            case 'abortive':
                $ret = "<span style='color: red;'>Несостоявшаяся</span>";
            break;
            case 'part_held':
                $ret = "<span style='color: blue;'>Частично состоявшаяся</span>";
            break;
            case 'part_abortive':
                $ret = "<span style='color: orange;'>Частично несостоявшаяся</span>";
            break;
            case 'completed':
                $ret = "<span style='color: black;'>Завершенная</span>";
            break;
            default:
                $ret = '';
        }
        
        return $ret;
    }
    
    public function getProductPriceTotal($priceName)
    {
        $total = 0;

        foreach ($this->purchaseOrderProducts as $orderHasProduct) {
            $total += $orderHasProduct->quantity *
                (isset($orderHasProduct->$priceName) ? $orderHasProduct->$priceName : $orderHasProduct->product->$priceName);
        }

        return $total;
    }
    
    public function deleteReturn()
    {
        if ($this->paid_total) {
            foreach ($this->purchaseOrderProducts as $product) {
                $deposit = $this->user->deposit;
                $fund_balance = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $product->id])->one();
                Fund::setDeductionForOrder($product->product_feature_id, -$product->purchase_price, $product->quantity);
                $provider_balance = PurchaseProviderBalance::find()->where(['purchase_order_product_id' => $product->id])->one();
                if ($fund_balance) {
                    Account::swap(null, $deposit, $fund_balance->total, 'Возврат членского взноса', false);
                }
                if ($provider_balance) {
                    $provider_account = Account::findOne(['user_id' => $provider_balance->provider->user_id]);
                    $order_number = !empty($this->order_number) ? $this->order_number : $this->order_number_copy;
                    Account::swap($provider_account, $deposit, $provider_balance->total, 'Возврат пая по заявке №' . $order_number, false);
                }
            }
            $this->delete();
        }
    }
}
