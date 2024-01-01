<?php

use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;


function getPurchasesOld($bot, $chat_id) 
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

    $send = "Общий список Закупок.";
    
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