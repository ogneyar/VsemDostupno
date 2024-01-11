<?php

use app\models\CartTg;


function calculation($bot, $tg_id, $summa) {

    $cart = CartTg::find()->where(['tg_id' => $tg_id])-all();

    if ($cart && sizeof($cart) > 1) {

        //
        
        // return;
    }

    // else
    
    $send = "При нажатии кнопки “Далее”, c Вашего лицевого счёта будет списана сумма ". $summa ."р., как обмен паями";
    
    $InlineKeyboardMarkup = [
        'inline_keyboard' =>  [
            [
                [
                    'text' => "Далее",
                    'callback_data' => 'calculationThen_' . $summa
                ],
            ],
            [
                [
                    'text' => "Отменить",
                    'callback_data' => 'cancelAPurchase'
                ],
            ],
        ]
    ];

    $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
}
