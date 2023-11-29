<?php

use app\models\CartTg;
use app\models\Product;
use app\models\ProductPrice;
use app\models\User;

require_once __DIR__ . '/getCart.php';


function deleteOneProduct($bot, $tg_id, $product_feature_id)
{
    $user = User::findOne(['tg_id' => $tg_id]);

    $cart = CartTg::findOne(['tg_id' => $tg_id, 'product_feature_id' => $product_feature_id]);

    $send = $cart->quantity . " ед. ";

    $product = Product::findOne($cart->product_id);

    if ( ! $cart->delete() ) {
        $bot->sendMessage($tg_id, "Ошибка! Не смог удалить товар.");
        return;
    }

    $productPrice = ProductPrice::findOne(['product_feature_id' => $product_feature_id]);
    $price = 0;
    if ( ! $user || $user->lastname == "lastname") {
        $price = $productPrice->price;
    }else {
        $price = $productPrice->member_price;
    }

    $send .= $product->name . " - " . $price . " за 1 шт. - вернулась на полку магазина из Вашей корзины";
    
    $bot->sendMessage($tg_id, $send);

    getCart($bot, $tg_id);

    return;
}