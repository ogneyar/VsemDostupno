<?php

use Yii;
use yii\base\Exception;
use app\models\CartTg;
use app\models\ProductFeature;
use app\models\TgCommunication;

require_once __DIR__ . '/cart/getCart.php';


function putInTheBasket($bot, $from_id, $product_feature_id, $quantity = 0) 
{
    if ($quantity == 0) {
        $tg_com = TgCommunication::findOne(['chat_id' => $from_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $from_id;
        $tg_com->to_chat_id = $from_id;
        $tg_com->from_whom = "putInTheBasket_" . $product_feature_id;
        $tg_com->save();

        // $send = "В строке “Сообщение” укажите желаемое количество едениц товара, цифрой, и отправьте её для сбора в Вашу корзину покупок";
        $send = "Укажите количество единиц";
        $bot->sendMessage($from_id, $send);
        return;
    }

    
    $productFeature = ProductFeature::findOne($product_feature_id);
    $product_id = $productFeature->product_id;

    $cart_tg = null;
    $carts_tg = CartTg::find()->where(['tg_id' => $from_id])->all();
    
    $transaction = Yii::$app->db->beginTransaction();
    try {

        foreach($carts_tg as $cart) {
            if ($cart->product_feature_id == $product_feature_id) {
                $cart_tg = $cart;
            }else if ($cart->last_choice) {
                $cart->last_choice = 0;                
                if ( ! $cart->save() ) {
                    throw new Exception("Не смог изменить корзину!");
                }
            }
        }
        if ( ! $cart_tg ) {
            $cart_tg = new CartTg();
        }
        $cart_tg->tg_id = $from_id;
        $cart_tg->product_id = $product_id;
        $cart_tg->product_feature_id = $product_feature_id;
        if ($cart_tg->quantity) $cart_tg->quantity += $quantity;
        else $cart_tg->quantity = $quantity;
        $cart_tg->last_choice = 1;
        if ( ! $cart_tg->save() ) {
            throw new Exception("Не смог сохранить корзину!!");
        }
        
        $transaction->commit();

    } catch (Exception $e) {
        
        $transaction->rollBack();
        
        $send = "Transaction ERROR! (putInTheBasket)\r\n";
        $send .= "Error message: " . $e->getMessage();
        
        $bot->sendMessage($from_id, $send);

        return;
    }
    
    $allPrices = 0;

    
    $send = "⭐️⭐️⭐️⭐️⭐️";       
    $ReplyKeyboardMarkup = [
        'keyboard' => [ 
            [
                [ 'text' => 'Показать закупки по начатой дате' ],
            ],        
            [
                [ 'text' => 'Показать все категории закупок' ],
            ],
        ],
        'resize_keyboard' => true,
        'selective' => true,
    ];
    $bot->sendMessage($from_id, $send, null, $ReplyKeyboardMarkup);


    getCart($bot, $from_id);
}
