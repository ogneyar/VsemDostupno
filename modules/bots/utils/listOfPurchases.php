<?php

use app\models\CartTg;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;


function listOfPurchases($bot, $from_id, $purchase_id, $step = 1, $show_menu = false, $without_search = false) 
{
    if ($show_menu) {
        $carts_tg = CartTg::find()->where(['tg_id' => $from_id])->all();

        $send = "⭐️⭐️⭐️⭐️⭐️";
        $keyboard = [];

        if ( ! $without_search ) $keyboard[] =  [ [ 'text' => 'Быстрый поиск товара' ], ];

        $keyboard[] =  [ [ 'text' => 'Показать закупки по начатой дате' ], ];
        $keyboard[] =  [ [ 'text' => 'Показать все категории закупок' ], ];

        if ($carts_tg) {
            $keyboard[] =  [ [ 'text' => 'В корзине товар' ], ];
        }

        $KeyboardMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
        ];
        $bot->sendMessage($from_id, $send, null, $KeyboardMarkup);
    }
    
    $max_quantity_raw = 7;

    $purchaseProduct = PurchaseProduct::findOne($purchase_id);
    
    $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
    $product_id = $productFeature->product_id;
    $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);
    $category_id = $categoryHasProduct->category_id;
    $category = Category::findOne($category_id);

    $inline_keyboard = [];
    $purchaseProducts = PurchaseProduct::find()->where(['purchase_date' => $purchaseProduct->purchase_date])->andWhere(['status' => 'advance'])->all();
    $quantity = 0;
    foreach($purchaseProducts as $purchaseProduct) {   
        $productFeature = ProductFeature::findOne($purchaseProduct->product_feature_id);
        $product_id = $productFeature->product_id;
        $product = Product::findOne($product_id);
        $productName = $product->name;
        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $product_id]);

        if ($categoryHasProduct->category_id == $category_id && $product->visibility) {
            $quantity++;
            if ($quantity <= (($step - 1)*$max_quantity_raw)) continue;
            if ($quantity > ($step*$max_quantity_raw)) continue;

            $inline_keyboard[] = [
                [
                    'text' => $productName,
                    'callback_data' => 'productWithAPhoto_' . $productFeature->id
                ],
            ];
            
        }
    }

    // $send = "И тут должен быть какой-то текст...";
    $send = $category->name;
    $InlineKeyboardMarkup = [ 'inline_keyboard' => $inline_keyboard, ];
    $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        
    if ($quantity > ($step*$max_quantity_raw)) {        
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
    }else {

        $send =  "Всё! Вы просмотрели весь ассортимент $category->name";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Просмотреть заново",
                        'callback_data' => 'listOfPurchases_' . $purchase_id
                    ],
                ],                
            ]
        ];

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);
    }

}
