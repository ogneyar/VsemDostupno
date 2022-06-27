<?php

namespace app\modules\site\controllers\profile;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\helpers\Html;
use yii\web\ForbiddenHttpException;
use app\modules\site\controllers\BaseController;
use app\models\Account;
use app\models\AccountLog;
use app\models\Email;
use app\models\Transfer;
use app\models\User;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\modules\site\models\account\TransferForm;
use app\modules\site\models\profile\partner\OrderForm;
use app\models\ProductFeature;
use app\models\Order;
use app\models\OrderStatus;
use app\models\OrderHasProduct;
use app\models\Product;
use app\models\ProviderHasProduct;
use app\models\ProviderStock;
use app\models\Fund;
use app\models\StockBody;
use app\models\NoticeEmail;
use app\models\SubscriberMessages;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\purchase\models\PurchaseFundBalance;
use app\modules\purchase\models\PurchaseProduct;


class AccountController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index',
                            'swap',
                            'transfer',
                            'order-create',
                            'success',
                            'subscriber',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
                            }

                            if (Yii::$app->user->identity->entity->disabled) {
                                $action->controller->redirect('/profile/logout')->send();
                                exit();
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $user = $this->identity->entity;

        $myAccounts = [];
        $accountTypes = ArrayHelper::getColumn($user->accounts, 'type');

        foreach ($accountTypes as $accountType) {
            $account = $user->getAccount($accountType);
            if ($account->type != Account::TYPE_GROUP 
                && $account->type != Account::TYPE_GROUP_FEE 
                && $account->type != Account::TYPE_FRATERNITY 
                && $account->type != Account::TYPE_SUBSCRIPTION 
                && $account) {
                $myAccounts[] = [
                    'name' => Html::makeTitle($account->typeName),
                    'account' => $account,
                    'actionEnable' => $account->type == Account::TYPE_DEPOSIT,
                    'dataProvider' => new ActiveDataProvider([
                        'id' => $account->type,
                        'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account->id]),
                        'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                        'pagination' => [
                            'params' => array_merge($_GET, [
                                'type' => $account->type,
                            ]),
                        ],
                    ]),
                ];
            }
        }

        $groupAccounts = [];
        if ($user->role == User::ROLE_PARTNER) {

            $partner_id = Partner::findOne(['user_id' => $user->id])->id;
            // можно так
            $members = Member::findAll(['partner_id' => $partner_id]);
            // или так
            // $members = Member::find()->where(['partner_id' => $partner_id])->all();
            // или вот так
            // $members = Member::find()->where('partner_id = :partner_id', [':partner_id' => $partner_id])->all();
            
            $total = 0;
            if ($members) {
                foreach ($members as $member) {
                    $total += Account::find()->where(['user_id' => $member->user_id])->andWhere(['type' => Account::TYPE_DEPOSIT])->one()->total;
                }
            }
            
            $groupAccounts[] = [
                'name' => 'Общая сумма расчётных счётов группы',
                'total' => $total,
                'members' => $members,
                'actionEnable' => false,
            ];

            $total = 0;
            if ($members) {
                foreach ($members as $member) {
                    $total += Account::find()->where(['user_id' => $member->user_id])->andWhere(['type' => Account::TYPE_SUBSCRIPTION])->one()->total;
                }
            }

            $groupAccounts[] = [
                'name' => 'Членские взносы группы',
                // 'total' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_GROUP_FEE)->total, 
                'total' => $total,
                'members' => null,
                'actionEnable' => false,
            ];

        }

        $fraternityAccount = [];
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            $fraternityAccount[] = [
                // 'name' => 'Отчисленно в фонд содружества',
                'name' => 'Счёт содружества',
                'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_FRATERNITY),
                'actionEnable' => false,
            ];
        }

        $accountType = Yii::$app->getRequest()->getQueryParam('type');
        if ($accountType == Account::TYPE_RECOMENDER) {

        }else if (!$user->getAccount($accountType)) {
            $accountType = Account::TYPE_DEPOSIT;
        }

        $долг = Yii::$app->user->identity->entity->getAccount(Account::TYPE_SUBSCRIPTION)->total;
        if ( $долг == 0 ) {
            $user = User::find()->where(['disabled' => 0, 'role' => [User::ROLE_SUPERADMIN]])->one();
            $account = Account::find()->where(['user_id' => $user->id,'type' => 'subscription'])->one();
            $долг = floor($account->total);
        }

        $subscription = [
            // 'name' => 'Ежемесячные членские взносы',
            'name' => 'Членский взнос',
            'account' => $долг, 
            'actionEnable' => false,
        ];

        // Рекомендательский сбор идёт на Инвестиционный счёт (TYPE_BONUS)
        $account_id = $user->getAccount(Account::TYPE_BONUS)->id;
        $info[] = [
            'name' => "Рекомендательские взносы",
            'actionEnable' => false,
            'recomender' => true,
            'dataProvider' => new ActiveDataProvider([
                'id' => Account::TYPE_RECOMENDER,
                'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account->id])->andWhere('message = "Рекомендательские взносы"'), 
                'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                'pagination' => [
                    'params' => array_merge($_GET, [
                        'type' => Account::TYPE_RECOMENDER,
                    ]),
                ],
            ]),
        ];
        // Членские взносы идут с Расчётного счёта (TYPE_DEPOSIT)
        $account_id = $user->getAccount(Account::TYPE_DEPOSIT)->id;
        $info[] = [
            'name' => "Членские взносы",
            'actionEnable' => false,
            'dataProvider' => new ActiveDataProvider([
                'id' => Account::TYPE_SUBSCRIPTION,
                'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account_id])->andWhere('message = "Членский взнос"'),
                'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                'pagination' => [
                    'params' => array_merge($_GET, [
                        'type' => Account::TYPE_SUBSCRIPTION,
                    ]),
                ],
            ]),
        ];

        return $this->render('index', [
            'title' => 'Счета',
            'myAccounts' => $myAccounts,
            'groupAccounts' => $groupAccounts,
            'fraternityAccount' => $fraternityAccount,
            'subscription' => $subscription,
            'info' => $info,
            'accountType' => $accountType,
            'user' => $user,
        ]);
    }


    public function actionTransfer()
    {
        $get = Yii::$app->request->get();
        if (isset($get['token'])) {
            $transfer = Transfer::findOne(['token' => $get['token']]);

            if ($transfer && !$transfer->fromAccount->user->disabled) {
                $result = Account::swap(
                    $transfer->fromAccount,
                    $transfer->toAccount,
                    $transfer->amount,
                    $transfer->message
                );
                $transfer->delete();

                if ($result) {
                    Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-finish');
                    return $this->redirect('/profile/message');
                }
            }

            Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-fail');
            return $this->redirect('/profile/message');
        }

        $model = new TransferForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transfer = new Transfer();
            $account = $this->identity->entity->deposit;
            $transfer->from_account_id = $account->id;
            $account = Account::find()
                ->where('user_id = :user_id AND type = :type', [
                    ':user_id' => $model->to_user_id,
                    ':type' => Account::TYPE_DEPOSIT,
                ])
                ->one();
            $transfer->to_account_id = $account->id;
            $transfer->amount = $model->amount;
            $transfer->message = $model->message;

            if ($transfer->save()) {
                Email::send('confirm-transfer', $this->identity->entity->email, ['url' => $transfer->url]);
                Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-success');
            } else {
                Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-fail');
            }

            return $this->redirect('/profile/message');
        }

        $toUserFullName = '(Кликните, чтобы задать пользователя)';
        if ($model->to_user_id) {
            $user = User::findOne($model->to_user_id);
            if ($user) {
                $toUserFullName = $user->fullName;
            }
        }

        return $this->render('transfer', [
            'title' => 'Перевести пользователю сайта',
            'model' => $model,
            'user' => $this->identity->entity,
            'toUserFullName' => $toUserFullName,
        ]);
    }

    
    public function actionOrderCreate()
    {
        $model = new OrderForm();
        $total_paid_for_provider = 0;

        $account = Account::find()->where(['user_id' => Yii::$app->user->id,'type' => 'subscription'])->one();            
        if ($account->total > 0) {
            Yii::$app->session->setFlash('message', 'Необходимо внести членский взнос!');
            return $this->render('order-create', [
                'title' => 'Быстрый заказ',
                'model' => $model,
            ]);
        }
		
        // if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        if ($model->load(Yii::$app->request->post())) {

            // if (!$model->validate()) return $this->redirect(['/profile/account/success']);
            if (!$model->validate()) return true;

            if (Yii::$app->user->identity->entity->role == User::ROLE_MEMBER || Yii::$app->user->identity->entity->role == User::ROLE_PROVIDER) {
                $is_purchase = isset($_POST['is_purchase']);
                $user = User::findOne(Yii::$app->user->id);

                $productList = Json::decode($model->product_list);
                $productList = ArrayHelper::map($productList, 'id', 'quantity');

                if ($is_purchase) {
                    $products = PurchaseProduct::find()
                        ->joinWith('productFeature')
                        ->joinWith('productFeature.product')
                        ->joinWith('productFeature.productPrices')
                        ->where(['IN', 'purchase_product.id', array_keys($productList)])
                        ->all();
                } else {
                    $products = ProductFeature::find()
                        ->joinWith('product')
                        ->joinWith('productPrices')
                        ->where(['IN', 'product_feature.id', array_keys($productList)])
                        ->all();
                }
                foreach ($products as $index => $product) {
                    if ($is_purchase) {
                        $products[$index] = $product->productFeature;
                        $products[$index]->purchase_product_id = $product->id;
                    }
                    $products[$index]->cart_quantity = $productList[$product->id];
                }
                $total = 0;
                foreach ($products as $product) {
                    if ($product->is_weights == 1) {
                        $total += $product->cart_quantity * $product->volume * $product->productPrices[0]->member_price;
                    } else {
                        $total += $product->cart_quantity * $product->productPrices[0]->member_price;
                    }
                }

                if ($total > $user->deposit->total) {
                    throw new ForbiddenHttpException('Недостаточно средств на счете для совершения покупки!');
                }

                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($is_purchase) {
                        $order = new PurchaseOrder;
                    } else {
                        $order = new Order();
                    }

                    $order->email = $user->email;
                    $order->phone = $user->phone;
                    $order->firstname = $user->firstname;
                    $order->lastname = $user->lastname;
                    $order->patronymic = $user->patronymic;
                    if ($user->role == User::ROLE_MEMBER) $order->comment = 'Заказ сделан через панель участника.';
                    if ($user->role == User::ROLE_PROVIDER) $order->comment = 'Заказ сделан через панель поставщика.';
                    $order->paid_total = $total;
                    $order->total = $total;

                    $member = Member::find()->where(['user_id' => $user->id])->one();

                    $partner = Partner::find()->where(['id' => $member->partner_id])->one();

                    $order->partner_id = $partner->id;
                    $order->partner_name = $partner->name;
                    $order->city_id = $partner->city->id;
                    $order->city_name = $partner->city->name;

                    $order->user_id = $user->id;
                    $order->role = $user->role;

                    if (!$is_purchase) {
                        $orderStatus = OrderStatus::findOne(['type' => OrderStatus::STATUS_NEW]);
                        $order->order_status_id = $orderStatus->id;
                    }

                    if (!$order->save()) {
                        throw new Exception('Ошибка сохранения заказа!');
                    }

                    foreach ($products as $product) {
                        if (!$product->quantity && $product->product->orderDate && (strtotime($product->product->orderDate) + strtotime('1 day', 0)) < time()) {
                            throw new Exception('"' . $product->product->name . '" нельзя заказать!');
                        }

                        if (!$is_purchase) {
                            if (isset($product->quantity)) {
                                if ($product->is_weights == 1) {
                                    $product->quantity -= $product->volume * $product->cart_quantity;
                                } else {
                                    $product->quantity -= $product->cart_quantity;
                                }

                                if ($product->quantity < 0) {
                                    throw new Exception('Ошибка обновления количества товара в магазине!');
                                }
                                
                                if (!$product->save()) {
                                    throw new Exception('Ошибка обновления количества товара в магазине!');
                                }
                            }
                        }

                        if ($is_purchase) {
                            $orderHasProduct = new PurchaseOrderProduct;
                            $orderHasProduct->purchase_order_id = $order->id;
                            $orderHasProduct->purchase_product_id = $product->purchase_product_id;
                            $orderHasProduct->status = 'advance';
                        } else {
                            $orderHasProduct = new OrderHasProduct();
                            $orderHasProduct->order_id = $order->id;
                        }
                        $orderHasProduct->product_id = $product->product_id;
                        $orderHasProduct->name = $product->product->name;
                        
                        if (!$is_purchase) {
                            $orderHasProduct->orderDate = $product->product->orderDate;
                            $orderHasProduct->purchaseDate = $product->product->purchaseDate;
                            $orderHasProduct->storage_price = 0;
                            $orderHasProduct->invite_price = 0;
                            $orderHasProduct->fraternity_price = 0;
                            $orderHasProduct->group_price = 0;
                            $orderHasProduct->purchase = 0;
                        }
                        
                        $orderHasProduct->price = $product->productPrices[0]->member_price;
                        $orderHasProduct->purchase_price = $product->purchase_price;
                        $orderHasProduct->product_feature_id = $product->id;
                        
                        if ($product->is_weights == 1) {
                            $orderHasProduct->quantity = $product->volume * $product->cart_quantity;
                        } else {
                            $orderHasProduct->quantity = $product->cart_quantity;
                        }
                        $orderHasProduct->total = $orderHasProduct->quantity * $product->productPrices[0]->member_price;
                        

                        $provider = ProviderHasProduct::find()->where(['product_id' => $product->product_id])->one();
                        $provider_id = $provider ? $provider->provider_id : 0;

                        if ($provider_id != 0) {
                            $orderHasProduct->provider_id = $provider_id;
                            
                            $provider_model = Provider::findOne(['id' => $provider_id]);
                            $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);

                            if (!$is_purchase) {
                                $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);
                                if ($stock_provider && !$product->product->isPurchase()) {
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
                                        if (!Account::swap($user->deposit, $provider_account, $paid_for_provider, 'Перевод пая на счёт', false)) {
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
                        }
                        
                        if (!$orderHasProduct->save()) {
                            throw new Exception('Ошибка сохранения товара в заказе!');
                        }
                        
                        if ($is_purchase) {
                            $provider_balance = new PurchaseProviderBalance;
                            $provider_balance->provider_id = $provider_id;
                            $provider_balance->user_id = $user->id;
                            $provider_balance->purchase_order_product_id = $orderHasProduct->id;
                            $provider_balance->total = $orderHasProduct->quantity * $orderHasProduct->purchase_price;
                            $provider_balance->save();
                            
                            PurchaseFundBalance::setDeductionForOrder($orderHasProduct->id, $user->id);
                            
                            $total_paid_for_provider += $provider_balance->total;
                            if (!Account::swap($user->deposit, $provider_account, $provider_balance->total, 'Перевод пая на счёт', false)) {
                                throw new Exception('Ошибка модификации счета пользователя!');
                            }
                        }
                    }

                    if ($order->paid_total > 0) {
                        if ($order->paid_total == $order->total) {
                            //$message = sprintf('Списано по заказу №%s.', $order->id);
                        } else {
                            //$message = sprintf('Частичная списано по заказу №%s.', $order->id);
                        }
                        
                        $message = 'Членский взнос';

                        if (!Account::swap($user->deposit, null, $order->paid_total - $total_paid_for_provider, $message, !$is_purchase)) {
                            throw new Exception('Ошибка модификации счета пользователя!');
                        }
                        if ($user->role == User::ROLE_PROVIDER) {
                            ProviderStock::setStockSum($user->id, $order->paid_total);
                        }
                        
                        if ($is_purchase) {
                            $deposit = $user->deposit;
                            $message = 'Списание на закупку';
                            Email::send('account-log', $deposit->user->email, [
                                'typeName' => $deposit->typeName,
                                'message' => $message,
                                'amount' => -$order->paid_total,
                                'total' => $deposit->total,
                            ]);
                        }
                    }
                    
                    if (!$is_purchase) Fund::setDeductionForOrder($product->id, $product->purchase_price, $product->cart_quantity);

                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();

                    throw new ForbiddenHttpException($e->getMessage());
                }

                if (!$is_purchase) {
                    $orderId = $order->id;
                    $order = Order::findOne($orderId);
                    $orderId = sprintf("%'.05d\n", $order->order_id);
                    
                    if ($emails = NoticeEmail::getEmails()) {
                        Email::send('order-customer', $emails, [
                            'id' => $orderId,
                            'information' => $order->htmlEmailFormattedInformation,
                        ]);
                    }

                    Email::send('order-customer', $order->email, [
                        'id' => $orderId,
                        'information' => $order->htmlEmailFormattedInformation,
                    ]);
                }
                
                // if ($is_purchase) {
                //     return $this->redirect(['/profile/provider/order/index']);
                // }
                // return $this->redirect(['/profile/partner/order/index']);


                return $this->redirect(['/profile/account/success']);

            }

        } else {

            return $this->render('order-create', [
                'title' => 'Быстрый заказ',
                'model' => $model,
            ]);
        }
    }

    
    public function actionSuccess()
    {
        return $this->render('success', [
            'title' => 'Ваша заявка отправлена!',
        ]);
    }
    
    public function actionSubscriber()
    {

        $constants = require(__DIR__ . '/../../../../config/constants.php');
        $web = $constants["WEB"];

        // $superadmin = false;
        // if (Yii::$app->user->identity->role == User::ROLE_SUPERADMIN) $superadmin = true;
        
        $user = User::find()->where(['disabled' => 0, 'role' => [User::ROLE_SUPERADMIN]])->one();
        $account = Account::find()->where(['user_id' => $user->id,'type' => 'subscription'])->one();
        
        $subscriber_messages = [];
        $subscriber = SubscriberMessages::find()->all();

        if (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            $partner = Partner::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
            
            foreach ($subscriber as $subscribe) {
                // if ($subscribe->user_id === 345) array_push($subscriber_messages, $subscribe);
                if ($subscribe->partner->id === $partner->id) array_push($subscriber_messages, $subscribe);
            }

        }else {
            $subscriber_messages = $subscriber;
        }
        


        return $this->render('subscriber', [
            'title' => 'Членские взносы группы!',
            'account' => floor($account->total),
            // 'superadmin' => $superadmin,
            'web' => $web,
            // 'request' => Yii::$app->request,
            'subscriber_messages' => $subscriber_messages,
        ]);
    }

}
