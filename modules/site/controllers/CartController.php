<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\base\Exception;
use app\models\Cart;
use app\models\Order;
use app\models\OrderHasProduct;
use app\models\Page;
use app\models\Partner;
use app\models\User;
use app\models\Member;
use app\modules\site\models\OrderForm;
use app\models\Email;
use app\models\Account;
use app\models\OrderStatus;
use app\models\StockHead;
use app\models\StockBody;
use yii\db\Query;
use app\models\ProviderStock;
use app\models\Provider;
use app\models\UnitContibution;
use app\models\ProviderHasProduct;
use app\models\Fund;
use app\models\NoticeEmail;
use app\models\Category;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\purchase\models\PurchaseFundBalance;
use app\modules\purchase\models\PurchaseProduct;


class CartController extends BaseController
{
    public function behaviors()
    {
        $enableCart = false;
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->role == User::ROLE_PROVIDER) {
                $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
                if ($member) {
                    $enableCart = true;
                }
            }
        }
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['order'],
                        'matchCallback' => function ($rule, $action) {
                            if (Cart::isEmpty()) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) use ($enableCart) {
                            if (!Yii::$app->user->isGuest && in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN]) && !$enableCart) { //  User::ROLE_PROVIDER,
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new Cart();
        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();

        return $this->render('index', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }

    public function actionOrder()
    {
        if (!Yii::$app->user->isGuest) {
            $cart = new Cart();
            $deposit = Yii::$app->user->identity->entity->deposit;

            if ($cart->total > $deposit->total) {
                Yii::$app->session->setFlash('message', 'Недостаточно средств на счете для совершения заказа!');

                return $this->redirect('/cart');
            }
        } else {
            $deposit = null;
        }

        $total_paid_for_provider = 0;
        $model = new OrderForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $stock_orders = $purchase_orders = [];
            $stock_orders_total = $purchase_orders_total = 0;
            $cart = new Cart();
            
            foreach ($cart->products as $p) {
                if ($p->product->isPurchase()) {
                    $purchase_orders[] = $p;
                    $purchase_orders_total += $p->calculatedTotalPrice;
                } else {
                    $stock_orders[] = $p;
                    $stock_orders_total += $p->calculatedTotalPrice;
                }
            }
            
            if (count($stock_orders)) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $order = new Order();

                    $order->email = $model->email;
                    $order->phone = '+' . preg_replace('/\D+/', '', $model->phone);
                    $order->firstname = $model->firstname;
                    $order->lastname = $model->lastname;
                    $order->patronymic = $model->patronymic;
                    $order->address = $model->address;
                    $order->comment = $model->comment;
                    if (!Yii::$app->user->isGuest) {
                        $order->paid_total = $stock_orders_total;
                    }

                    if ($model->partner) {
                        $partner = Partner::findOne($model->partner);

                        if ($partner) {
                            $order->partner_id = $partner->id;
                            $order->partner_name = $partner->name;
                            $order->city_id = $partner->city->id;
                            $order->city_name = $partner->city->name;
                        }
                    } elseif (!Yii::$app->user->isGuest) {
                        if (in_array(Yii::$app->user->identity->role, [User::ROLE_PARTNER])) {
                            $partner = Yii::$app->user->identity->entity->partner;
                            $order->city_id = $partner->city->id;
                            $order->city_name = $partner->city->name;
                        }
                    }

                    if (!Yii::$app->user->isGuest) {
                        $entity = Yii::$app->user->identity->entity;
                        $order->user_id = $entity->id;
                        $order->role = $entity->role;
                        
                        if ($entity->role == User::ROLE_PROVIDER) {
                            $member = Member::find()->where(['user_id' => $entity->id])->one();
                            if ($member) {
                                $order->role = User::ROLE_MEMBER;
                            }
                        }
                    }
                    
                    $order->total = $stock_orders_total;
                    $orderStatus = OrderStatus::findOne(['type' => OrderStatus::STATUS_NEW]);
                    $order->order_status_id = $orderStatus->id;

                    if (!($order->save())) {
                        throw new Exception('Ошибка сохранения заказа!');
                    }

                    foreach ($stock_orders as $product) {
                        if ($product->is_weights == 1) {
                            $product->quantity -= $product->volume * $product->cart_quantity;
                        } else {
                            $product->quantity -= $product->cart_quantity;
                        }
                        
                        if ($product->quantity < 0) {
                            throw new Exception('Ошибка обновления количества товара в магазине!');
                        }

                        if (!$product->save()) {
                            throw new Exception('Ошибка сохранения количества товара в магазине!');
                        }
                        
                        $orderHasProduct = new OrderHasProduct();
                        $orderHasProduct->order_id = $order->id;
                        $orderHasProduct->product_id = $product->product_id;
                        $orderHasProduct->name = $product->product->name;
                        $orderHasProduct->orderDate = $product->product->orderDate;
                        $orderHasProduct->purchaseDate = $product->product->purchaseDate;
                        $orderHasProduct->storage_price = 0;
                        $orderHasProduct->invite_price = 0;
                        $orderHasProduct->fraternity_price = 0;
                        $orderHasProduct->group_price = 0;
                        $orderHasProduct->purchase = 0;
                        
                        $orderHasProduct->price = $product->getCalculatedPrice(false);
                        $orderHasProduct->purchase_price = $product->purchase_price;
                        $orderHasProduct->product_feature_id = $product->id;
                        
                        if ($product->is_weights == 1) {
                            $orderHasProduct->quantity = $product->volume * $product->cart_quantity;
                        } else {
                            $orderHasProduct->quantity = $product->cart_quantity;
                        }
                        $orderHasProduct->total = $product->calculatedTotalPrice;
                        
                        $provider = ProviderHasProduct::find()->where(['product_id' => $product->product_id])->one();
                        $provider_id = $provider ? $provider->provider_id : 0;

                        if ($provider_id != 0) {
                            $orderHasProduct->provider_id = $provider_id;
                            
                            $provider_model = Provider::findOne(['id' => $provider_id]);
                            $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);

                            $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);
                            if ($stock_provider) {
                                if ($stock_provider->reaminder_rent >= $orderHasProduct->quantity) {
                                    $stock_provider->reaminder_rent -= $orderHasProduct->quantity;
                                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                    $paid_for_provider = $orderHasProduct->quantity * $body->summ;
                                    $stock_provider->summ_on_deposit += $paid_for_provider;
                                    $stock_provider->save();
                                } else {
                                    $rest = $orderHasProduct->quantity - $stock_provider->reaminder_rent;
                                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                    $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                                    $stock_provider->reaminder_rent = 0;
                                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                    $stock_provider->save();
                                    
                                    while ($rest > 0) {
                                        $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);
                                        
                                        if ($stock_provider->reaminder_rent >= $rest) {
                                            $stock_provider->reaminder_rent -= $rest;
                                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                            $paid_for_provider = $rest * $body->summ;
                                            $stock_provider->summ_on_deposit += $paid_for_provider;
                                            $stock_provider->save();
                                            $rest = 0;
                                        } else {
                                            $rest -= $stock_provider->reaminder_rent;
                                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                            $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                                            $stock_provider->reaminder_rent = 0;
                                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                            $stock_provider->save();
                                        }
                                    }
                                }
                                
                                if ($body->deposit == '1') {
                                    $paid_for_provider = $orderHasProduct->quantity * $body->summ;
                                    if (!Account::swap($deposit, $provider_account, $paid_for_provider, 'Произведён обмен паями по заявке №' . sprintf("%'.05d\n", $order->order_id), false)) {
                                        throw new Exception('Ошибка модификации счета пользователя!');
                                    }
                                    Email::send('account-log', $provider_account->user->email, [
                                        'message' => 'Перевод пая на счёт',
                                        'amount' => $paid_for_provider,
                                        'total' => $provider_account->total,
                                    ]);
                                    $total_paid_for_provider += $paid_for_provider;
                                }
                            }
                        }
                        if (!$orderHasProduct->save()) {
                            throw new Exception('Ошибка сохранения товара в заказе!');
                        }

                        Fund::setDeductionForOrder($product->id, $product->purchase_price, $product->cart_quantity);

                    }

                    if ($order->paid_total > 0) {
                        if ($order->paid_total == $order->total) {
                            //$message = sprintf('Списано по заказу №%s.', $order->id);
                        } else {
                            //$message = sprintf('Частичная списано по заказу №%s.', $order->id);
                        }
                        $message = 'Членский взнос';

                        if (!Account::swap($deposit, null, $order->paid_total - $total_paid_for_provider, $message)) {
                           throw new Exception('Ошибка модификации счета пользователя!');
                        }
                        if ($entity->role == User::ROLE_PROVIDER) {
                            ProviderStock::setStockSum($entity->id, $order->paid_total);
                        }
                    }
                    
                    // Fund::setDeductionForOrder($product->id, $product->purchase_price, $product->cart_quantity);

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();

                    // echo $e;
                    // return null;

                    //throw new ForbiddenHttpException($e->getMessage());
                    Yii::$app->session->setFlash('cart-checkout', [
                        'name' => 'cart-checkout-fail',
                    ]);

                    return $this->redirect('checkout');
                }
                $orderId = $order->id;
                $order = Order::findOne($orderId);
                $orderId = sprintf("%'.05d\n", $order->order_id);
                
                if ($emails = NoticeEmail::getEmails()) {
                    Email::send('order-customer', $emails, [
                        'id' => $orderId,
                        'information' => $order->htmlEmailFormattedInformation,
                    ]);
                }

                if ($order->partner) {
                    Email::send('order-partner', $order->partner->email, [
                        'id' => $orderId,
                        'information' => $order->htmlEmailFormattedInformation,
                    ]);
                }

                Email::send('order-customer', $order->email, [
                    'id' => $orderId,
                    'information' => $order->htmlEmailFormattedInformation,
                ]);
                
                $order->formattedTotal = Yii::$app->formatter->asCurrency($cart->total, 'RUB');
                $order->order_id = $orderId;
                Yii::$app->session->setFlash('cart-checkout', [
                    'name' => 'cart-checkout-success',
                    'order' => $order,
                ]);
            }
            
            if (count($purchase_orders)) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $order = new PurchaseOrder;

                    $order->email = $model->email;
                    $order->phone = '+' . preg_replace('/\D+/', '', $model->phone);
                    $order->firstname = $model->firstname;
                    $order->lastname = $model->lastname;
                    $order->patronymic = $model->patronymic;
                    $order->address = $model->address;
                    $order->comment = $model->comment;
                    if (!Yii::$app->user->isGuest) {
                        $order->paid_total = $cart->total;
                    }

                    if ($model->partner) {
                        $partner = Partner::findOne($model->partner);

                        if ($partner) {
                            $order->partner_id = $partner->id;
                            $order->partner_name = $partner->name;
                            $order->city_id = $partner->city->id;
                            $order->city_name = $partner->city->name;
                        }
                    } elseif (!Yii::$app->user->isGuest) {
                        if (in_array(Yii::$app->user->identity->role, [User::ROLE_PARTNER])) {
                            $partner = Yii::$app->user->identity->entity->partner;
                            $order->city_id = $partner->city->id;
                            $order->city_name = $partner->city->name;
                        }
                    }

                    if (!Yii::$app->user->isGuest) {
                        $entity = Yii::$app->user->identity->entity;
                        $order->user_id = $entity->id;
                        $order->role = $entity->role;
                        
                        if ($entity->role == User::ROLE_PROVIDER) {
                            $member = Member::find()->where(['user_id' => $entity->id])->one();
                            if ($member) {
                                $order->role = User::ROLE_MEMBER;
                            }
                        }
                    }
                    
                    $order->total = $purchase_orders_total;
                    if (!($order->save())) {
                        throw new Exception('Ошибка сохранения заказа!');
                    }

                    foreach ($purchase_orders as $product) {
                        $purchase = PurchaseProduct::getPurchaseDateByFeature($product->id);
                        $orderHasProduct = new PurchaseOrderProduct;
                        $orderHasProduct->purchase_order_id = $order->id;
                        $orderHasProduct->purchase_product_id = $purchase[0]->id;
                        $orderHasProduct->product_id = $product->product_id;
                        $orderHasProduct->name = $product->product->name;
                        $orderHasProduct->price = $product->getCalculatedPrice(false);
                        $orderHasProduct->purchase_price = $product->purchase_price;
                        $orderHasProduct->product_feature_id = $product->id;
                        $orderHasProduct->status = 'advance';
                        
                        if ($product->is_weights == 1) {
                            $orderHasProduct->quantity = $product->volume * $product->cart_quantity;
                        } else {
                            $orderHasProduct->quantity = $product->cart_quantity;
                        }
                        $orderHasProduct->total = $product->calculatedTotalPrice;
                        
                        $provider = ProviderHasProduct::find()->where(['product_id' => $product->product_id])->one();
                        $provider_id = $provider ? $provider->provider_id : 0;

                        if ($provider_id != 0) {
                            $orderHasProduct->provider_id = $provider_id;
                            $provider_model = Provider::findOne(['id' => $provider_id]);
                            $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);
                        }
                        if (!$orderHasProduct->save()) {
                            throw new Exception('Ошибка сохранения товара в заказе!');
                        }
                        
                        $provider_balance = new PurchaseProviderBalance;
                        $provider_balance->provider_id = $provider_id;
                        if (!Yii::$app->user->isGuest) {
                            $provider_balance->user_id = $entity->id;
                        }
                        $provider_balance->purchase_order_product_id = $orderHasProduct->id;
                        $provider_balance->total = $orderHasProduct->quantity * $orderHasProduct->purchase_price;
                        $provider_balance->save();
                        
                        if (!Yii::$app->user->isGuest) {
                            PurchaseFundBalance::setDeductionForOrder($orderHasProduct->id, $entity->id);
                        } else {
                            PurchaseFundBalance::setDeductionForOrder($orderHasProduct->id, null);
                        }
                        
                        $total_paid_for_provider += $provider_balance->total;
                        if (!Account::swap($deposit, $provider_account, $provider_balance->total, 'Перевод пая на счёт', false)) {
                            throw new Exception('Ошибка модификации счета пользователя!');
                        }

                        Fund::setDeductionForOrder($product->id, $product->purchase_price, $product->cart_quantity);

                    }

                    if ($order->paid_total > 0) {
                        if ($order->paid_total == $order->total) {
                            //$message = sprintf('Списано по заказу №%s.', $order->id);
                        } else {
                            //$message = sprintf('Частичная списано по заказу №%s.', $order->id);
                        }
                        $message = 'Членский взнос';

                        if (!Account::swap($deposit, null, $order->paid_total - $total_paid_for_provider, $message, false)) {
                           throw new Exception('Ошибка модификации счета пользователя!');
                        }
                        if ($entity->role == User::ROLE_PROVIDER) {
                            ProviderStock::setStockSum($entity->id, $order->paid_total);
                        }
                        
                        $deposit = Yii::$app->user->identity->entity->deposit;
                        $message = 'Списание на закупку';
                        Email::send('account-log', $deposit->user->email, [
                            'typeName' => $deposit->typeName,
                            'message' => $message,
                            'amount' => -$order->paid_total,
                            'total' => $deposit->total,
                        ]);
                    }
                    
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();

                    //throw new ForbiddenHttpException($e->getMessage());
                    Yii::$app->session->setFlash('cart-checkout', [
                        'name' => 'cart-checkout-fail',
                    ]);

                    return $this->redirect('/cart/checkout');
                }
                Email::send('add_advance_order', $order->email, [
                    'fio' => Yii::$app->user->isGuest ? $order->firstname . ' ' . $order->patronymic : $entity->respectedName,
                    'order_products' => $order->htmlEmailFormattedInformation,
                    'order_number' => $order->order_number,
                ]);
                
                $order->formattedTotal = Yii::$app->formatter->asCurrency($cart->total, 'RUB');
                Yii::$app->session->setFlash('cart-checkout', [
                    'name' => 'cart-purchase-checkout-success',
                    'order' => $order,
                ]);
            }
            $cart->clear();

            return $this->redirect('/cart/checkout');
        } else {
            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
            return $this->render('order', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
            ]);
        }
    }

    public function actionCheckout()
    {
        $data = Yii::$app->session->getFlash('cart-checkout');

        if (!$data) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = Page::findOne(['slug' => $data['name']]);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if (isset($data['order'])) {
            $attributes = array_keys($data['order']->attributeLabels());
            $patterns = [];
            $replacements = [];

            foreach ($attributes as $attribute) {
                $patterns[] = '/{{%' . $attribute . '}}/';
                $replacements[] = $data['order']->$attribute;
            }

            $model->title = preg_replace($patterns, $replacements, $model->title);
            $model->content = preg_replace($patterns, $replacements, $model->content);
        }

        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
        return $this->render('checkout', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }
}
