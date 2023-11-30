<?php

use app\models\Image;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\ProductHasPhoto;
use app\models\Photo;
use app\models\User;

use app\modules\purchase\models\PurchaseProduct;


function productWithAPhoto($bot, $from_id, $product_feature_id) 
{
    $user = User::findOne(['tg_id' => $from_id]);
    
    $productPrice = ProductPrice::findOne(['product_feature_id' => $product_feature_id]);
    $productFeature = ProductFeature::findOne($product_feature_id);

    $product_id = $productFeature->product_id;
    $productHasPhoto = ProductHasPhoto::findOne(['product_id' => $product_id]);
    $photoId = $productHasPhoto->photo_id;
    $photo = Photo::findOne($photoId);
    $image_id = $photo->image_id;
    $image = Image::findOne($image_id);
    $file = $image->file;

    $purchaseProduct = PurchaseProduct::find()
        ->where(['product_feature_id' => $productFeature->id])
        ->andWhere(['status' => 'advance'])
        ->one();

    if ( ! $purchaseProduct ) {
        $send = "Товар не найден!";
        $bot->sendMessage($from_id, $send);
        return;
    }
  
    if (! $user || $user->lastname == "lastname") {
        $send = $productPrice->price . "р.";
    }else {
        $send = $productPrice->member_price . "р.";
    }

    if ($feature->is_weights) {
        $send .= " за 1 кг.";
    }else {
        $send .=  " за 1 шт.";
    }

    $InlineKeyboardMarkup = [
        'inline_keyboard' => [                            
            [
                [
                    'text' => "Описание",
                    'callback_data' => 'productDescription_' . $product_id
                ],
                [
                    'text' => "Отменить",
                    'callback_data' => 'cancelAPurchase'
                ],
            ],      
            [
                [
                    'text' => "Положить в корзину",
                    'callback_data' => 'putInTheBasket_' . $product_feature_id
                ],
            ],          
        ]
    ];

    $bot->sendPhoto($from_id, "https://будь-здоров.рус/web" . $file, $send, null, $InlineKeyboardMarkup);
      
}
