<?php

use \app\models\Product;

function getDescription($bot, $tg_id, $product_id) {

    // $send = "Ознакомьтесь с краткой информацией";
    
    // $InlineKeyboardMarkup = [
    //     'inline_keyboard' => [
    //         [
    //             'text' => "Фото поставщика",
    //             'callback_data' => 'photoProvider_' . $provider_id
    //         ],
    //         [
    //             [
    //                 'text' => "Свойства товара",
    //                 'callback_data' => 'getDescription_' . $category->id
    //             ],
    //         ],
    //     ]
    // ];

    // $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
    // return;

    $product = Product::findOne($product_id);

    // $bot->sendMessage($tg_id, $product->description);
    $bot->sendMessage($tg_id, "Не реализовано!");

}