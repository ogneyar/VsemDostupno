<?php

use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\Provider;
use app\modules\purchase\models\PurchaseProduct;


function listOfPurchases($bot, $from_id, $purchase_id, $step = 1) {
    
    $purchaseProduct = PurchaseProduct::findOne($purchase_id);
    $provider_id = $purchaseProduct->provider_id;
    $provider = Provider::findOne($provider_id);
    $providerName = $provider->name;
    $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
    $product_id = $productFeature->product_id;
    $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);
    $category_id = $categoryHasProduct->category_id;
    $category = Category::findOne($category_id);
    $categoryName = $category->name;

    $send = date('d.m.Y', strtotime($purchaseProduct->purchase_date)) . "г., состоится закупка " . $categoryName . " от " . $providerName;

    $bot->sendMessage($from_id, $send);    

    $purchaseProducts = PurchaseProduct::find()->where(['purchase_date' => $purchaseProduct->purchase_date])->andWhere(['status' => 'advance'])->all();
    $quantity = 0;
    foreach($purchaseProducts as $purchaseProduct) {   
        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        $productPrice = ProductPrice::findOne(['product_feature_id' => $productFeature->id]);
        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);

        if ($categoryHasProduct->category_id == $category_id && $product->visibility) {
            $quantity++;
            if ($quantity <= (($step - 1)*4)) continue;
            if ($quantity > ($step*4)) continue;
            $send = $productName . "\r\n" . $productPrice->price . " / " . $productPrice->member_price;

            $InlineKeyboardMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "Выбрать",
                            'callback_data' => 'productWithAPhoto_' . $productFeature->id
                        ],
                    ],
                ],
            ];
            $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
        }
    }

        
    if ($quantity > ($step*4)) {        
        $step++;
        $send =  "Остальной перечень";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Смотреть",
                        'callback_data' => 'listOfPurchases_' . $purchase_id . "_" . $step
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }

}
