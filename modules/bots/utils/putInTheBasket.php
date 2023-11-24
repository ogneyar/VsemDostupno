<?php

use app\models\CartTg;
// use app\models\Image;
use app\models\Product;
use app\models\ProductPrice;
// use app\models\ProductHasPhoto;
// use app\models\Photo;
use app\models\TgCommunication;
use app\models\User;


function putInTheBasket($bot, $from_id, $product_id, $quantity = 0) 
{
    
    $user = User::findOne(['tg_id' => $from_id]);

    // if ( ! $user ) {
    //     $send = "Для совершения покупок Вам необходимо пройти регистрацию!";
    //     $bot->sendMessage($from_id, $send);
    //     return;
    // }

    if ($quantity == 0) {
        $tg_com = TgCommunication::findOne(['chat_id' => $from_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $from_id;
        $tg_com->to_chat_id = $from_id;
        $tg_com->from_whom = "putInTheBasket_" . $product_id;
        $tg_com->save();

        $send = "В строке “Сообщение” укажите желаемое количество едениц товара, цифрой, и отправьте её для сбора в Вашу корзину покупок";
        $bot->sendMessage($from_id, $send);
        return;
    }

    $cart_tg = null;
    $carts_tg = CartTg::find()->where(['tg_id' => $from_id])->all();
    foreach($carts_tg as $cart) {
        if ($cart->product_id == $product_id) {
            $cart_tg = $cart;
        }
    }
    if ( ! $cart_tg ) {
        $cart_tg = new CartTg();
    }
    $cart_tg->tg_id = $from_id;
    $cart_tg->product_id = $product_id;
    $cart_tg->quantity = $quantity;
    $cart_tg->save();
    
    $allPrices = 0;
    $send = "У Вас в корзине:\r\n\r\n";
    $carts_tg = CartTg::find()->where(['tg_id' => $from_id])->all();
    foreach($carts_tg as $cart) {
        $product_id = $cart->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        $productPrice = ProductPrice::findOne(['product_id' => $product_id]);
        if (! $user || $user->lastname == "lastname") {
            $price = $productPrice->price;
        }else {
            $price = $productPrice->member_price;
        }
        $allPrices += $price * $cart->quantity;

        $send .= $cart->quantity . " еденицы " . $productName . " - " . $price . " за 1 шт.\r\n\r\n";
    }

    $send .= "На общую сумму " . $allPrices . "р. \r\n\r\n";
    // $send .= "Доставка товара состоится 12.11.23г.";
    
    $InlineKeyboardMarkup = [
        'inline_keyboard' => [
            [
                [
                    'text' => "Расчёт",
                    'callback_data' => 'calculation_' . $allPrices
                ],
            ],
            [
                [
                    'text' => "Продолжить выбор",
                    'callback_data' => 'continueSelection_' . $product_id // !!!!!!! НЕ РЕАЛИЗОВАНО !!!!!!
                ],
            ],
            [
                [
                    'text' => "Отменить",
                    'callback_data' => 'cancelAPurchase' // !!!!!!! НЕ РЕАЛИЗОВАНО !!!!!!
                ],
            ],
        ],
    ];

    $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
}
