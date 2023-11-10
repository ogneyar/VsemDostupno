<?php


// use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
// use app\models\ProviderHasProduct;
// use app\models\Provider;
use app\modules\purchase\models\PurchaseProduct;


function listOfProducts($bot, $from_id, $provider_id, $category_id, $step = 1) {
    $purchaseProducts = PurchaseProduct::find()->where(['provider_id' => $provider_id])->andWhere(['status' => 'advance'])->all();
    $iter = 0;
    foreach($purchaseProducts as $purchaseProduct) {       
        $iter++;
        if ($iter <= (($step - 1)*4)) continue;
        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        $productPrice = ProductPrice::findOne(['product_id' => $product_id]);
        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);
        if ($categoryHasProduct->category_id == $category_id) {
            $send = $productName . " – " . $productPrice->price . " / " . $productPrice->member_price;

            $InlineKeyboardMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => "Положить в корзину",
                            'callback_data' => 'putInTheBasket_' . $product_id
                        ],
                    ],                
                ]
            ];

            $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
        }
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
                        'callback_data' => 'listOfProducts_' . $provider_id . '_' . $category->id . "_" . $step
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }

}
