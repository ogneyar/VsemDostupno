<?php

use Yii;
use yii\base\Exception;
use app\models\Account;
use app\models\CartTg;
use app\models\Email;
use app\models\Member;
use app\models\Partner;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\Provider;
use app\models\ProviderHasProduct;
use app\models\ProviderStock;
use app\models\User;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\purchase\models\PurchaseFundBalance;
use app\modules\purchase\models\PurchaseProduct;

require_once __DIR__ . '/cart/getCart.php';
require_once __DIR__ . '/cart/clearCart.php';


function purchaseOrderCreate($bot, $from_id, $summa)
{
    $total_paid_for_provider = 0;

    $cartTg = CartTg::find()->where(['tg_id' => $from_id])->all();


    $arrayProducts = [];
    $error = false;
    foreach($cartTg as $cart) {        
        $feature = ProductFeature::findOne($cart->product_feature_id);
        $purchase = PurchaseProduct::find()
            ->where(['product_feature_id' => $cart->product_feature_id])
            ->andWhere(['status' => 'advance'])
            ->one();
            
        $product = Product::findOne($cart->product_id);
        $productPrice = ProductPrice::findOne(['product_feature_id' => $feature->id]);

        if ( ! $purchase ) {
            $error = true;
            $cart->delete();
        }else {
            $arrayProducts[] = [
                'cart' => $cart,
                'product' => $product,
                'feature' => $feature,
                'price' => $productPrice,
                'purchase' => $purchase,
            ];
        }
    }

    if ($error) {
        $send = "Расчёт не был произведён, так как корзина была изменена!\r\n";
        $send .= "Товаров, положенных в корзину вами ранее, уже нет в наличии.";
        $bot->sendMessage($from_id, $send);
        getCart($bot, $from_id);

        return;
    }

    $user = User::findOne(['tg_id' => $from_id]);              
    if ( ! $user ) {
        $send = "Для совершения покупок необходимо зарегистрироваться!";
        $bot->sendMessage($from_id, $send);
        return;
    }

    $account = Account::find()->where(['user_id' => $user->id,'type' => 'subscription'])->one();            
    if ( ! $account || $account->total > 0) {
        $send = "Необходимо внести членский взнос!";
        $bot->sendMessage($from_id, $send);
        return;
    }


    $total = 0;
    foreach ($arrayProducts as $product) {
        if ($user->lastname == "lastname") $price = $product['price']->price;
        else $price = $product['price']->member_price;
        if ($product['feature']->is_weights == 1) {
            $total += $product['cart']->quantity * $product['feature']->volume * $price;
        } else {
            $total += $product['cart']->quantity * $price;
        }
    }

    if ($total != $summa) {
        $send = "Произошёл перерасчёт корзины из-за изменившихся цен!";
        $bot->sendMessage($from_id, $send);
        getCart($bot, $from_id);

        return;
    }

    if ($total > $user->deposit->total) {
        $send = "На Вашем счёте не достаточно средств!";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Отменить",
                        'callback_data' => 'cancelAPurchase'
                    ],
                ],
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    $transaction = Yii::$app->db->beginTransaction();

    try {

        // throw new Exception('Test!');

        $order = new PurchaseOrder;
        
        $order->email = $user->email;
        $order->phone = $user->phone;
        $order->firstname = $user->firstname;
        $order->lastname = $user->lastname ? $user->lastname : "-";
        $order->patronymic = $user->patronymic;
        $order->comment = 'Заказ сделан через telegram.';
        $order->paid_total = $total;
        $order->total = $total;

        if ($user->member) {
            $partner = $user->member->partner;
            $order->partner_id = $partner->id;
            $order->partner_name = $partner->name;
        } elseif ($user->partner) {
            $partner = $user->partner;
        }else {
            $partner = Partner::findOne(1);
        }

        $order->city_id = $partner->city->id;
        $order->city_name = $partner->city->name;
        $order->user_id = $user->id;
        $order->role = $user->role;

        if ($user->role == User::ROLE_PROVIDER) {
            $member = Member::find()->where(['user_id' => $user->id])->one();
            if ($member) {
                $order->role = User::ROLE_MEMBER;
            }
        }
        
        if (!$order->save()) {
            throw new Exception('Ошибка сохранения заказа!');
        }

        foreach ($arrayProducts as $product) {
            // if (!$product->quantity && $product->product->orderDate && (strtotime($product->product->orderDate) + strtotime('1 day', 0)) < time()) {
            //     throw new Exception('"' . $product->product->name . '" нельзя заказать!');
            // }
            
            $orderHasProduct = new PurchaseOrderProduct;
            $orderHasProduct->purchase_order_id = $order->id;
            $orderHasProduct->purchase_product_id = $product['purchase']->id;
            $orderHasProduct->status = 'advance';
                
            $orderHasProduct->product_id = $product['product']->id;
            $orderHasProduct->name = $product['product']->name;
            
            $orderHasProduct->price = $product['price']->member_price;
            $orderHasProduct->purchase_price = $product['price']->purchase_price;
            $orderHasProduct->product_feature_id = $product['feature']->id;
            
            if ($product->is_weights == 1) {
                $orderHasProduct->quantity = $product['feature']->volume * $product['cart']->quantity;
            } else {
                $orderHasProduct->quantity = $product['cart']->quantity;
            }

            if ($user->lastname == "lastname") $price = $product['price']->price;
            else $price = $product['price']->member_price;

            $orderHasProduct->total = $orderHasProduct->quantity * $price;
            
            $provider = ProviderHasProduct::find()->where(['product_id' => $product['product']->id])->one();
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
            $provider_balance->user_id = $user->id;
            $provider_balance->purchase_order_product_id = $orderHasProduct->id;
            $provider_balance->total = $orderHasProduct->quantity * $orderHasProduct->purchase_price;
            $provider_balance->save();
            
            PurchaseFundBalance::setDeductionForOrder($orderHasProduct->id, $user->id);
            
            $total_paid_for_provider += $provider_balance->total;
            
            // $message = "Перевод пая на счёт";
            // if (!Account::transfer($user->deposit, $user, $provider_account, -$provider_balance->total, $message, true)) {
            //     throw new Exception('Ошибка сохранения счета Покупателя!');
            // }
            // if (!Account::transfer($provider_account, $user, $provider_account->user, $provider_balance->total, $message, false)) {
            //     throw new Exception('Ошибка сохранения счета Продавца!');
            // }

            if (!Account::swap($user->deposit, $provider_account, $provider_balance->total, 'Перевод пая на счёт', false)) {
                throw new Exception('Ошибка модификации счета пользователя!');
            }
                
        }


        if ($order->paid_total > 0) {            
            $message = 'Членский взнос';

            if (!Account::swap($user->deposit, null, $order->paid_total - $total_paid_for_provider, $message, false)) {
                throw new Exception('Ошибка модификации счета пользователя!');
            }
            if ($user->role == User::ROLE_PROVIDER) {
                ProviderStock::setStockSum($user->id, $order->paid_total);
            }
            
            $deposit = $user->deposit;
            $message = 'Списание на закупку';
              
            if ($user->tg_id) {
                Email::tg_send('account-log', $user->tg_id, [
                    'typeName' => $deposit->typeName,
                    'message' => $message,
                    'amount' => -$order->paid_total,
                    'total' => $deposit->total,
                ]);       
            }            
        }

        
        $transaction->commit();
    } catch (Exception $e) {
        $transaction->rollBack();
        
        $send = "Transaction ERROR! (purchaseOrderCreate)\r\n";
        $send .= "Error message: " . $e->getMessage();
        
        $bot->sendMessage($from_id, $send);

        return;
    }


    $arrayPurchases = [];

    foreach ($arrayProducts as $product) {

        $purchase_date = strtotime($product['purchase']->purchase_date);

        $yes = false;
        foreach ($arrayPurchases as $purchase) {
            if ($purchase['purchase_date'] == $purchase_date)
            {
                $yes = true;
                $purchase['data'][] = $product;
            }
        }

        if (!$yes) {
            $arrayPurchases[] = [
                'purchase_date' => $purchase_date,
                'data' => [
                    $product
                ]
            ];
        }

    }

    foreach ($arrayPurchases as $purchase) {
        $total = 0;
        $text = "";
        foreach ($purchase['data'] as $product) {          
            if ($user->lastname == "lastname") $price = $product['price']->price;
            else $price = $product['price']->member_price;

            $text .= $product['cart']->quantity . " ед. " . $product['product']->name . " - " . $price ."р. ";  

            if ($product['feature']->is_weights == 1) {
                $total += $product['cart']->quantity * $product['feature']->volume * $price;
                $text .= "за кг.\r\n";
            } else {
                $total += $product['cart']->quantity * $price;
                $text .= "за шт.\r\n";
            }
        }
        
        // $send = date('d.m.Y') . " Вами произведён обмен паями на общую сумму " . $total . "р.\r\n";
        $send = date('d.m.Y') . " Вами произведён обмен паями\r\n";
        $send .= $text;
        $send .= "На общую сумму " . $total . "р.\r\n";
        $send .= "Доставка товара состоится " . date('d.m.Y', $purchase['purchase_date']);

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Распечатать акт",
                        'callback_data' => 'printTheAct_' //. $product_id
                    ],
                ],                
                [
                    [
                        'text' => "Заказать доставку на дом",
                        'callback_data' => 'homeDelivery_' //. $product_id
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }
    

        
    clearCart($from_id);
   
    // $bot->sendMessage($from_id, "конец!");
    // return;
        
}
  