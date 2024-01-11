<?php

use app\models\CartTg;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;


// ПЕРЕЧЕНЬ ЗАКУПОК по номеру родительской категории
function getPurchasesByParentId($bot, $chat_id, $parent_id, $show_menu = true) 
{      
    $products = PurchaseProduct::find()->where(['status' => 'advance'])->all();

    if ( ! $products[0] ) {
        $send = "Нет действующих закупок.";
        $bot->sendMessage($chat_id, $send);
        return;
    }

    if ($show_menu) {
        $carts_tg = CartTg::find()->where(['tg_id' => $chat_id])->all();

        $send = "⭐️⭐️⭐️⭐️⭐️";
        $keyboard = [
            [
                [ 'text' => 'Быстрый поиск товара' ],
            ],            
            [
                [ 'text' => 'Показать все категории закупок' ],
            ],            
        ];

        if ($carts_tg) {
            $keyboard[] =  [
                [ 'text' => 'В корзине товар' ],
            ];
        }

        $KeyboardMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
        ];
        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);
    }


    $category_parent_name = "";

    $allCategories = [];
    foreach($products as $product) {
        $feature_id = $product->product_feature_id;
        $product_feature = ProductFeature::findOne($feature_id);
        $real_product_id = $product_feature->product_id;
        $real_product = Product::findOne($real_product_id);
        if ($real_product->visibility == 0) continue;
        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $real_product_id]);
        $category_id = $categoryHasProduct->category_id;
        $category = Category::findOne($category_id);
        $category_parent_id = $category->parent;
        $category_parent = Category::findOne($category_parent_id);
        if ($category_parent_id != $parent_id) continue;

        // “Продукты” “Промтовары” “Здоровье”
        $category_parent_name =  mb_convert_case(mb_strtolower($category_parent->name), MB_CASE_TITLE, "UTF-8");
        
        // $bot->sendMessage($chat_id, $category_parent->id . " - " . $category_parent_id . " - " . $category_parent_name);   

        $yes = false;
        foreach($allCategories as $oneCategory) {
            if ($oneCategory['category_id'] == $category_id && $oneCategory['purchase_date'] == strtotime($product->purchase_date)) $yes = true;
        }
        if ( ! $yes ) $allCategories[] = [
            'category_id' => $category_id, 
            'category_name' => $category->name,
            'purchase_id' => $product->id, 
            'purchase_date' => strtotime($product->purchase_date), 
        ];
    }

    usort($allCategories, function($a, $b) {
        if ($a['category_name'] > $b['category_name']) {
            return 1;
        } elseif ($a['category_name'] < $b['category_name']) {
            return -1;
        }
        return 0;
    });
    
    usort($allCategories, function($a, $b) {
        if ($a['purchase_date'] > $b['purchase_date']) {
            return 1;
        } elseif ($a['purchase_date'] < $b['purchase_date']) {
            return -1;
        }
        return 0;
    });

    $send = "Общий список $category_parent_name.";
    
    $inline_keyboard = [];
    foreach($allCategories as $oneCategory) {
        $text =  $oneCategory['category_name'] . " " . date('d.m.Y', $oneCategory['purchase_date']); 
    
        $inline_keyboard[] = [
            [
                'text' => $text,
                'callback_data' => 'listOfPurchases_' . $oneCategory['purchase_id']
            ],
        ];
    }
    
    $InlineKeyboardMarkup = [
        'inline_keyboard' => $inline_keyboard
    ];
    $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);    

    return;

}