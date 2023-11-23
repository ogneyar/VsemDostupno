<?php


use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
// use app\models\ProviderHasProduct;
use app\models\Provider;
use app\modules\purchase\models\PurchaseProduct;


// function listOfPurchases($bot, $from_id, $provider_id, $category_id, $step = 1) {
function listOfPurchases($bot, $from_id, $purchase_id, $step = 1) {
    
    $purchaseProduct = PurchaseProduct::findOne($purchase_id);
    // $bot->sendMessage($from_id, date('d.m.Y', $purchase_date));
    // $bot->sendMessage($from_id, date('d.m.Y', strtotime($purchaseProduct->purchase_date)));

    if ($step == 1) {
        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;
        // $product = Product::findOne($product_id);
        // $productName = $product->name;
        // $productPrice = ProductPrice::findOne(['product_id' => $product_id]);
        $provider_id = $purchaseProduct->provider_id;
        $provider = Provider::findOne($provider_id);
        $providerName = $provider->name;
        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);
        $category_id = $categoryHasProduct->category_id;
        $category = Category::findOne($category_id);
        $categoryName = $category->name;

        $send = date('d.m.Y', strtotime($purchaseProduct->purchase_date)) . "г., состоится закупка " . $categoryName . " от " . $providerName;

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                // [
                //     [
                //         'text' => "Смотреть весь перечень товаров",
                //         'callback_data' => 'listOfOroductsWithADescription_' . $product_id
                //     ], 
                // ],      
                [
                    [
                        'text' => "Масло семечек тыквы 0.5л – 240.00 / 195.00",
                        'callback_data' => 'listOfOroductsWithAphoto_' . $product_id
                    ],
                ],      
                [
                    [
                        'text' => "Масло семечек арбуза 0.5л. – 240.00 / 195.00",
                        'callback_data' => 'listOfOroductsWithAphoto_' . $product_id
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }

    
    // $allCategories = [];
    // foreach($products as $product) {
    //     $feature_id = $product->product_feature_id;
    //     $product_feature = ProductFeature::findOne($feature_id);
    //     $real_product_id = $product_feature->product_id;
    //     $real_product = Product::findOne($real_product_id);
    //     if ($real_product->visibility == 0) continue;
    //     $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $real_product_id]);
    //     $category_id = $categoryHasProduct->category_id;
    //     $category = Category::findOne($category_id);
    //     $yes = false;
    //     foreach($allCategories as $oneCategory) {
    //         if ($oneCategory['category_id'] == $category_id && $oneCategory['purchase_date'] == strtotime($product->purchase_date)) $yes = true;
    //     }
    //     if ( ! $yes ) $allCategories[] = [
    //         'category_id' => $category_id, 
    //         'category_name' => $category->name,
    //         'purchase_id' => $product->id, 
    //         'purchase_date' => strtotime($product->purchase_date), 
    //     ];
    // }


    $purchaseProducts = PurchaseProduct::find()->where(['purchase_date' => $purchaseProduct->purchase_date])->andWhere(['status' => 'advance'])->all();
    $quantity = 0;
    foreach($purchaseProducts as $purchaseProduct) {   
        // $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        // $product_id = $productFeature->product_id;
        // $product = Product::findOne($product_id);
        // $productName = $product->name;
        // $productPrice = ProductPrice::findOne(['product_id' => $product_id]);
        // $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);
    //     if ($categoryHasProduct->category_id == $category_id && $product->visibility) {
    //         $quantity++;
    //         if ($quantity <= (($step - 1)*4)) continue;
    //         if ($quantity > ($step*4)) continue;
    //         $send = $productName . " – " . $productPrice->price . " / " . $productPrice->member_price;

    //         $InlineKeyboardMarkup = [
    //             'inline_keyboard' => [
    //                 [
    //                     [
    //                         'text' => "Положить в корзину",
    //                         'callback_data' => 'putInTheBasket_' . $product_id
    //                     ],
    //                 ],                
    //             ]
    //         ];

    //         $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    //     }
    }
        
    // if ($quantity > ($step*4)) {        
    //     $step++;
    //     $send =  "Остальной перечень";

    //     $InlineKeyboardMarkup = [
    //         'inline_keyboard' => [
    //             [
    //                 [
    //                     'text' => "Смотреть",
    //                     'callback_data' => 'listOfProducts_' . $provider_id . '_' . $category_id . "_" . $step
    //                 ],
    //             ],                
    //         ]
    //     ];

    //     $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    // }

}
