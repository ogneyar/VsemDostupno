<?php

use app\models\Image;
use app\models\Product;
use app\models\ProductPrice;
use app\models\ProductHasPhoto;
use app\models\Photo;
// use app\models\User;


function productWithAPhoto($bot, $from_id, $product_id) 
{
    $productPrice = ProductPrice::findOne(['product_id' => $product_id]);
    $productHasPhoto = ProductHasPhoto::findOne(['product_id' => $product_id]);
    $photoId = $productHasPhoto->photo_id;
    $photo = Photo::findOne($photoId);
    $image_id = $photo->image_id;
    $image = Image::findOne($image_id);
    $file = $image->file;
    
    if ($user->lastname == "lastname") {
        $send = $productPrice->price . "р.";
    }else {
        $send = $productPrice->member_price . "р.";

    }

    $InlineKeyboardMarkup = [
        'inline_keyboard' => [
            [
                [
                    'text' => "Положить в корзину",
                    'callback_data' => 'putInTheBasket_' . $product_id
                ],
            ],                
            [
                [
                    'text' => "Описание",
                    'callback_data' => 'productDescription_' . $product_id // !!!!!!! НЕ РЕАЛИЗОВАНО !!!!!!
                ],
            ],                
            [
                [
                    'text' => "Отменить",
                    'callback_data' => 'cancelAPurchase' // !!!!!!! НЕ РЕАЛИЗОВАНО !!!!!!
                ],
            ],                
        ]
    ];

    $bot->sendPhoto($from_id, "https://будь-здоров.рус/web" . $file, $send, null, $InlineKeyboardMarkup);
      
}
