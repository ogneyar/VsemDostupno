<?php

use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;


// получение списка родительских категорий
function getMainPurchases($bot, $chat_id) 
{  
    
    $products = PurchaseProduct::find()->where(['status' => 'advance'])->all();

    if ( ! $products[0] ) {
        $send = "Нет действующих закупок.";
        $bot->sendMessage($chat_id, $send);
        return;
    }

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

        // “Продукты” “Промтовары” “Здоровье”
        $category_parent_name =  mb_convert_case(mb_strtolower($category_parent->name), MB_CASE_TITLE, "UTF-8");
        // if ($category_parent_name == "Продукты") {
        //     $bot->sendMessage($chat_id, $category_parent_id);    
        //     break;
        // }

        $yes = false;
        foreach($allCategories as $oneCategory) {
            if ($oneCategory['category_parent_id'] == $category_parent_id) {
                $yes = true;
                if (strtotime($product->purchase_date) < $oneCategory['purchase_date']) {
                    $oneCategory = [
                        'category_parent_id' => $category_parent_id, 
                        'category_parent_name' => $category_parent_name,
                        // 'purchase_id' => $product->id, 
                        'purchase_date' => strtotime($product->purchase_date), 
                    ];
                }
            }
        }
        if ( ! $yes ) $allCategories[] = [
            'category_parent_id' => $category_parent_id, 
            'category_parent_name' => $category_parent_name,
            // 'purchase_id' => $product->id, 
            'purchase_date' => strtotime($product->purchase_date), 
        ];
    }

    usort($allCategories, function($a, $b) {
        if ($a['purchase_date'] > $b['purchase_date']) {
            return 1;
        } elseif ($a['purchase_date'] < $b['purchase_date']) {
            return -1;
        }
        return 0;
    });

    $send = "Общий список Закупок.";
    
    $inline_keyboard = [];
    foreach($allCategories as $oneCategory) {
        $text =  $oneCategory['category_parent_name'] . " " . date('d.m.Y', $oneCategory['purchase_date']); 
    
        $inline_keyboard[] = [
            [
                'text' => $text,
                'callback_data' => 'getPurchasesByParentId_' . $oneCategory['category_parent_id']
            ],
        ];
    }
    
    $InlineKeyboardMarkup = [
        'inline_keyboard' => $inline_keyboard
    ];
    $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);    

    return;

}