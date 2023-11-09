<?php


use app\models\Product;
use app\models\ProductPrice;
use app\models\ProviderHasProduct;


function editPricePurchase($bot, $from_id, $provider_id, $step) {
    $providerHasProducts = ProviderHasProduct::find()->where(['provider_id' => $provider_id])->all();
    
    if ( ! $providerHasProducts ) {
        $bot->sendMessage($from_id, "У поставщика нет товаров!");            
        return;
    }
    $iter = 0;
    foreach($providerHasProducts as $providerHasProduct) {            
        $iter++;
        if ($iter <= (($step - 1)*4)) continue;

        $product_id = $providerHasProduct->product_id;
        $product = Product::findOne($product_id);
        $productPrice = ProductPrice::findOne(['product_id' => $product_id]);

        $send =  $product->name . "\r\n";
        if ($productPrice->purchase_price) $send .= $productPrice->purchase_price . "р.";
        else $send .= "Нет цены";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Изменить цену",
                        'callback_data' => 'editpriceproduct_' . $product_id
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
        
        if ($iter == ($step*4)) break;
    }

    if (count($providerHasProducts) > ($step*4)) {        
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
