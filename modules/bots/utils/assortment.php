<?php

use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;


function assortment($bot, $from_id, $provider_id, $step = 1) {
    $purchaseProducts = PurchaseProduct::find()->where(['provider_id' => $provider_id])->andWhere(['status' => 'abortive'])->all();
    $quantity = 0;
    foreach($purchaseProducts as $purchaseProduct) {   
        $quantity++;
        if ($quantity <= (($step - 1)*4)) continue;
        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        
        $send = $product->name . "\r\n";

        $inline_keyboard = [];

        if ($product->visibility) {
            $send .= "В наличии";
            $inline_keyboard[] = [
                [
                    'text' => "Исключить",
                    'callback_data' => 'resetVisibleProduct_' . $product_id
                ],
            ];
        }else {
            $send .= "Нет в наличии";
            $inline_keyboard[] = [
                [
                    'text' => "Добавить",
                    'callback_data' => 'setVisibleProduct_' . $product_id
                ],
            ];
        }

        $InlineKeyboardMarkup = [
            'inline_keyboard' =>  $inline_keyboard
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
            
        if ($quantity >= ($step*4)) break;
    }
        
    if (count($purchaseProducts) > ($step*4)) {        
        $step++;
        $send =  "Остальной перечень";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Смотреть",
                        'callback_data' => 'assortment_' . $provider_id . "_" . $step
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }

}
