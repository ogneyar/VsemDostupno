<?php

use app\models\CartTg;
use app\models\Product;


function clearCartPartly($bot, $tg_id)
{
    $cartTg = CartTg::find()->where(['tg_id' => $tg_id])->all();

    if ($cartTg) {
        $send = "Исключите нужный продукт из корзины покупок";
        $bot->sendMessage($tg_id, $send);

        foreach($cartTg as $cart) {
            $product = Product::findOne($cart->product_id);
            $send = $product->name . " - " . $cart->quantity . " шт.";

            $inline_keyboard = [];

            if ($cart->quantity > 1) {
                $inline_keyboard[] = [ [ 'text' => "Удалить полностью", 'callback_data' => 'deleteOneProduct_' . $cart->product_feature_id ] ];
                $inline_keyboard[] = [ [ 'text' => "Частично", 'callback_data' => 'deleteOneProductPartly_' . $cart->product_feature_id ] ];
            }else  {
                $inline_keyboard[] = [ [ 'text' => "Удалить", 'callback_data' => 'deleteOneProduct_' . $cart->product_feature_id ] ];
            }

            $InlineKeyboardMarkup = [
                'inline_keyboard' => $inline_keyboard
            ];

            $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
        }
    }else {
        $send = "Ваша корзина пуста!";
        $bot->sendMessage($tg_id, $send);
    }

}
