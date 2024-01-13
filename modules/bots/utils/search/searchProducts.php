<?php

use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;

function searchProducts($bot, $tg_id, $search) {

    $purchaseProducts = PurchaseProduct::find()->where(['status' => 'advance'])->all();

    if ( ! $purchaseProducts ) {
        $send = "Ничего не найдено по искомому запросу:\r\n\r\n" . $search;
        $bot->sendMessage($tg_id, $send);
        return;
    }

    $found = false;
    // $arrayPurchases = [];
    $inline_keyboard = [];

    foreach ($purchaseProducts as $purchaseProduct) {
        $feature_id = $purchaseProduct->product_feature_id;
        $productFeature = ProductFeature::findOne($feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        
        if (mb_strpos(mb_strtolower($productName), mb_strtolower($search), 0, "UTF-8") !== false) {
            // array_push($arrayPurchases, $purchaseProduct);
            if ($product->visibility) {
                $found = true;
                $inline_keyboard[] = [
                    [
                        'text' => $productName,
                        'callback_data' => 'productWithAPhoto_' . $productFeature->id
                    ],
                ];                
            }
            // $send = "Урраааа нашёл!!!";
            // $bot->sendMessage($tg_id, $send);
            // break;
        }
    }

    if ( ! $found ) {
        $send = "Ничего не найдено по искомому запросу:\r\n\r\n" . $search;
        $bot->sendMessage($tg_id, $send);
        return;
    }

    $InlineKeyboardMarkup = [ 'inline_keyboard' => $inline_keyboard, ];
    $bot->sendMessage($tg_id, $search, null, $InlineKeyboardMarkup);
}
