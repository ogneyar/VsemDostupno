<?php

use app\models\CartTg;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\User;

use app\modules\purchase\models\PurchaseProduct;


function getCart($bot, $tg_id) 
{
    $user = User::findOne(['tg_id' => $tg_id]);

    $carts_tg = CartTg::find()->where(['tg_id' => $tg_id])->all();
    // $arrayPurchases = [];
    
    // foreach($carts_tg as $cart) {
    //     $purchase = PurchaseProduct::findOne(['product_feature_id' => $cart->product_feature_id]);
    //     $yes = false;
    //     foreach($arrayPurchases as $array) {
    //         if ($array['purchase_date'] == $purchase->purchase_date) {
    //             $yes = true;
    //             $array['carts'][] = [ $cart ];
    //         }
    //     }
    //     if ( ! $yes ) {
    //         $arrayPurchases[] = [
    //             'purchase_date' => $purchase->purchase_date,
    //             'carts' => [ $cart ],
    //         ];
    //     }
    // }
    
    // if ( ! count($arrayPurchases) ) {
    //     $bot->sendMessage($tg_id, "Ваша корзина пуста!");
    //     return $item;
    // }

    
    // foreach($arrayPurchases as $array) {
        
    //     $send = "У Вас в корзине:\r\n\r\n";

    //     $allPrices = 0;
        
    //     foreach($array['carts'] as $cart) {
            
    //         $product_id = $cart->product_id;
    //         $product = Product::findOne($product_id);
    //         $productName = $product->name;

    //         $productPrice = ProductPrice::findOne(['product_feature_id' => $cart->product_feature_id]);
    //         if (! $user || $user->lastname == "lastname") {
    //             $price = $productPrice->price;
    //         }else {
    //             $price = $productPrice->member_price;
    //         }
    //         $allPrices += $price * $cart->quantity;
    
    //         $send .= $cart->quantity . " еденицы " . $productName . " - " . $price . " за 1 шт.\r\n\r\n";
    //     }
        
    //     $send .= "На общую сумму " . $allPrices . "р. \r\n\r\n";

    //     $send .= "Доставка товара состоится ".date("d.m.Y", strtotime($array['purchase_date']))."г.";
        
    //     $InlineKeyboardMarkup = [
    //         'inline_keyboard' => [
    //             [
    //                 [
    //                     'text' => "Расчёт",
    //                     'callback_data' => 'calculation_' . '_' . strtotime($array['purchase_date']) . $allPrices
    //                 ],
    //             ],
    //             [
    //                 [
    //                     'text' => "Продолжить выбор",
    //                     'callback_data' => 'continueSelection'
    //                 ],
    //             ],
    //             [
    //                 [
    //                     'text' => "Отменить",
    //                     'callback_data' => 'cancelAPurchase'
    //                 ],
    //             ],
    //         ],
    //     ];

    //     $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);

    // }

    $item = 0;
    $send = "У Вас в корзине:\r\n\r\n";
    foreach($carts_tg as $cart) {
        
        $product_id = $cart->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;

        $item++;

        $productPrice = ProductPrice::findOne(['product_feature_id' => $cart->product_feature_id]);
        if (! $user || $user->lastname == "lastname") {
            $price = $productPrice->price;
        }else {
            $price = $productPrice->member_price;
        }
        $allPrices += $price * $cart->quantity;

        $send .= $cart->quantity . " еденицы " . $productName . " - " . $price . " за 1 шт.\r\n";

        $purchase = PurchaseProduct::findOne(['product_feature_id' => $cart->product_feature_id]);
        $purchase_date = $purchase->purchase_date;
        
        $send .= " (доставка: ".date('d.m.Y', strtotime($purchase_date)).")\r\n\r\n";
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
                    'callback_data' => 'continueSelection'
                ],
            ],
            [
                [
                    'text' => "Отменить",
                    'callback_data' => 'cancelAPurchase'
                ],
            ],
        ],
    ];

    $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
    
    return $item;
    // return count($arrayPurchases);
}