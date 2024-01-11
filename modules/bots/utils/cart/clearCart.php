<?php

use app\models\CartTg;
use app\models\Product;


function clearCart($tg_id, $bot = null, $send_info = false) 
{    
    $carts_tg = CartTg::find()->where(['tg_id' => $tg_id])->all();
    $error = 0;
    foreach($carts_tg as $cart) {
        if ( ! $cart->delete() && $bot) {
            $product = Product::findOne($cart->product_id);
            $bot->sendMessage($tg_id, "Не смог удалить товар - " .  $product->name .".");
            $error++;
        }
    }

    if ($bot) {
        if ($error) {
            if (count($carts_tg) == $error) $bot->sendMessage($tg_id, "Не смог очистить корзину!");
            else $bot->sendMessage($tg_id, "Корзина очищенна частично!");
        }else {
            if ($send_info) {

                $send = "Все товары вернулись на полку магазина, ваша корзина пуста!";
                $keyboard = [];
                
                $keyboard[] =  [ [ 'text' => 'Показать закупки по начатой дате' ], ];
                $keyboard[] =  [ [ 'text' => 'Показать все категории закупок' ], ];

                $KeyboardMarkup = [
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                ];
                $bot->sendMessage($from_id, $send, null, $KeyboardMarkup);

            }else $bot->sendMessage($tg_id, "Ваша корзина пуста!");
        }
    }
}
