<?php


use app\models\Product;
use app\models\ProductFeature;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\TgSavePurchaseDate;
use app\modules\purchase\models\PurchaseProduct;



function purchasesByTheStartedDate($bot, $tg_id) 
{
    $tgSavePurchaseDate = TgSavePurchaseDate::findOne(['chat_id' => $tg_id]);
    
    if ( ! $tgSavePurchaseDate ) {        
        $bot->sendMessage($tg_id, "Ошибка! Нет искомой даты закупки.");    
        return;
    }

    $purchase_date = $tgSavePurchaseDate->purchase_date;
    $purchase_date = date('Y-m-d', $purchase_date);    

    $products = PurchaseProduct::find()->where(['purchase_date' => $purchase_date])->andWhere(['status' => 'advance'])->all();

    if ( ! $products[0] ) {
        $send = "Уже нет действующих закупок на выбранную дату.";
        $bot->sendMessage($tg_id, $send);
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

    $send = "Закупки начатой даты.";
    
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
    $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);    
}