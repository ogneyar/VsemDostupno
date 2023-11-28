<?php

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

        $send = "В строке “Сообщение” укажите желаемое количество едениц товара, цифрой, и отправьте её для сбора в Вашу корзину покупок";
        $bot->sendMessage($from_id, $send);
        return;
    }

    
    $productFeature = ProductFeature::findOne($product_feature_id);
    $product_id = $productFeature->product_id;

    $cart_tg = null;
    $carts_tg = CartTg::find()->where(['tg_id' => $from_id])->all();
    foreach($carts_tg as $cart) {
        if ($cart->product_feature_id == $product_feature_id) {
            $cart_tg = $cart;
        }
    }
    if ( ! $cart_tg ) {
        $cart_tg = new CartTg();
    }
    $cart_tg->tg_id = $from_id;
    $cart_tg->product_id = $product_id;
    $cart_tg->product_feature_id = $product_feature_id;
    $cart_tg->quantity = $quantity;
    $cart_tg->save();
    
    $allPrices = 0;

    getCart($bot, $from_id);
}
