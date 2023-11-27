<?php

use app\models\CartTg;
use app\models\Product;
use app\models\ProductPrice;
use app\models\User;


function getCart($bot, $tg_id) 
{
    $user = User::findOne(['tg_id' => $tg_id]);

    $item = 0;
    $send = "У Вас в корзине:\r\n\r\n";
    $carts_tg = CartTg::find()->where(['tg_id' => $tg_id])->all();
    foreach($carts_tg as $cart) {
        $item++;
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

    if ( ! $item ) {
        $bot->sendMessage($tg_id, "Ваша корзина пуста!");
        return $item;
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

    $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
    
    return $item;
}