<?php

use app\models\CartTg;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;

function searchProducts($bot, $tg_id, $search, $withButtons = false) {

    $purchaseProducts = PurchaseProduct::find()->where(['status' => 'advance'])->all();

    $found = false;
    $inline_keyboard = [];

    foreach ($purchaseProducts as $purchaseProduct) {
        $feature_id = $purchaseProduct->product_feature_id;
        $productFeature = ProductFeature::findOne($feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        
        if (mb_strpos(mb_strtolower($productName), mb_strtolower($search), 0, "UTF-8") !== false) {
            if ($product->visibility) {
                $found = true;
                $inline_keyboard[] = [
                    [
                        'text' => $productName,
                        'callback_data' => 'productWithAPhoto_' . $productFeature->id
                    ],
                ];                
            }
        }
    }

    if ( ! $found ) {

        if ($withButtons) {
            $send = "⭐️⭐️⭐️⭐️⭐️";
            $keyboard = [ 
                [
                    [ 'text' => 'Показать закупки по начатой дате' ],
                ],        
                [
                    [ 'text' => 'Показать все категории закупок' ],
                ],
            ];
            
            $cart = CartTg::find()->where(['tg_id' => $tg_id])->all();

            if ($cart) {
                $keyboard[] =  [
                    [ 'text' => 'В корзине товар' ],
                ];
            }

            $keyboardMarkup = [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
            ];
            $bot->sendMessage($tg_id, $send, null, $keyboardMarkup);
        }
        // else {
        //     $send = "Ничего не найдено по запросу:\r\n\r\n" . $search;
        //     $bot->sendMessage($tg_id, $send);
        // }

        $send = "Ничего не найдено по запросу:\r\n\r\n" . $search;
            
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [                            
                [
                    [
                        'text' => "Искать другой товар?",
                        'callback_data' => 'searchProducts'
                    ],
                ],          
            ]
        ]; 
        $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    $InlineKeyboardMarkup = [ 'inline_keyboard' => $inline_keyboard, ];
    $bot->sendMessage($tg_id, $search, null, $InlineKeyboardMarkup);
}
