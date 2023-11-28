<?php


use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;

use app\modules\purchase\models\PurchaseProduct;


function editPricePurchase($bot, $from_id, $provider_id, $step) {   
    $purchaseProducts = PurchaseProduct::find()->where(['provider_id' => $provider_id])->andWhere(['status' => 'abortive'])->all();
    
    if ( ! $purchaseProducts ) {
        $bot->sendMessage($from_id, "У поставщика нет товаров!");            
        return;
    }

    $iter = 0;
    foreach($purchaseProducts as $purchaseProduct) {      
        $iter++;
        if ($iter <= (($step - 1)*4)) continue;

        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;

        $product = Product::findOne($product_id);
        $productPrice = ProductPrice::findOne(['product_feature_id' => $productFeature->id]);

        $send =  $product->name . "\r\n";
        if ($productPrice->purchase_price) $send .= $productPrice->purchase_price . "р.";
        else $send .= "Нет цены";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Изменить цену",
                        'callback_data' => 'editpriceproduct_' . $productFeature->id
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
        
        if ($iter == ($step*4)) break;
    }
     
    if (count($purchaseProducts) > ($step*4)) {        
        $step++;
        $send =  "Остальной перечень";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Смотреть",
                        'callback_data' => 'editpricepurchase_' . $provider_id . "_" . $step
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }
}
