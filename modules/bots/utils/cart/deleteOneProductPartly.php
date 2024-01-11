<?php

use app\models\CartTg;
use app\models\Product;
use app\models\ProductPrice;
use app\models\TgCommunication;
use app\models\User;

require_once __DIR__ . '/getCart.php';


function deleteOneProductPartly($bot, $tg_id, $product_feature_id, $quantity = 0) 
{
    if ($quantity == 0) {
        $tg_com = TgCommunication::findOne(['chat_id' => $tg_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $tg_id;
        $tg_com->to_chat_id = $tg_id;
        $tg_com->from_whom = "deleteOneProductPartly_" . $product_feature_id;
        $tg_com->save();

        $send = "В строке “Сообщение”, цифрой, укажите требуемое количество этого товара и отправьте уведомление для изменения";
        $bot->sendMessage($tg_id, $send);
        return;
    }
    
    $cart = CartTg::findOne(['tg_id' => $tg_id, 'product_feature_id' => $product_feature_id]);

    if ( ! $cart ) {        
        $bot->sendMessage($tg_id, "Ваша корзина пуста!");
        return;
    }
    
    $cart->quantity = $quantity;

    if ( ! $cart->save() ) {
        $bot->sendMessage($tg_id, "Ошибка! Не смог сохранить изменения!");
        return;
    }

    $product = Product::findOne($cart->product_id);

    $user = User::findOne(['tg_id' => $tg_id]);
    $productPrice = ProductPrice::findOne(['product_feature_id' => $product_feature_id]);

    $price = 0;
    if ( ! $user || $user == "lastname" ) {
        $price = $productPrice->price;
    }else {
        $price = $productPrice->member_price;
    }

    $send = $quantity . " ед. " . $product->name . " - " . $price . " за 1 шт. -  Уже в Вашей корзине";

    $keyboard = [
        [
            [ 'text' => 'Показать закупки по начатой дате' ],
        ],            
        [
            [ 'text' => 'Показать все категории закупок' ],
        ],            
    ];

    $KeyboardMarkup = [
        'keyboard' => $keyboard,
        'resize_keyboard' => true,
    ];

    $bot->sendMessage($tg_id, $send, null, $KeyboardMarkup);

    getCart($bot, $tg_id);

}