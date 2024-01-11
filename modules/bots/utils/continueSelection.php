<?php


use app\models\CartTg;
use app\models\Product;
use app\models\ProductFeature;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\modules\purchase\models\PurchaseProduct;

require_once __DIR__ . '/listOfPurchases.php';


function continueSelection($bot, $tg_id, $purchases_by_the_started_date = false) 
{
    $cart = CartTg::findOne(['tg_id' => $tg_id, 'last_choice' => 1]);

    $purchaseProduct = PurchaseProduct::findOne(['product_feature_id' => $cart->product_feature_id,'status' => 'advance']);
    $purchase_id = $purchaseProduct->id;

    if ( ! $purchase_id ) {        
        $bot->sendMessage($tg_id, "Ошибка! Нет искомой закупки.");    
        return;
    }

    if ( ! $purchases_by_the_started_date ) 
    {
    listOfPurchases($bot, $tg_id, $purchase_id, /*step=*/1, /*show_menu=*/true, /*without_search=*/true);
    
        return;
    }


    $purchase_date = $purchaseProduct->purchase_date;

    $products = PurchaseProduct::find()->where(['purchase_date' => $purchase_date])->andWhere(['status' => 'advance'])->all();

    if ( ! $products[0] ) {
        $send = "Уже нет действующих закупок на выбранную дату.";
        $bot->sendMessage($tg_id, $send);
        return;
    }
    
    // $provider = Provider::findOne($provider_id);

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