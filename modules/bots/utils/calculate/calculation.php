<?php

use app\models\CartTg;


function calculation($bot, $tg_id, $summa, $together = false) {

    // $cart = CartTg::find()->where(['tg_id' => $tg_id])->all();
    // if ($cart && sizeof($cart) > 1 && ! $together) {
    //     $send = "В вашей корзине товары с разными датами доставки. Желаете расплатиться отдельно по выбранным датам или оплатить всё вместе?";        
    //     $InlineKeyboardMarkup = [
    //         'inline_keyboard' =>  [
    //             [
    //                 [
    //                     'text' => "Отдельно",
    //                     'callback_data' => 'calculationByDate'
    //                 ],
    //             ],
    //             [
    //                 [
    //                     'text' => "Вместе",
    //                     'callback_data' => 'calculation_' .  $summa . '_true'
    //                 ],
    //             ],
    //         ]
    //     ];
    //     $bot->sendMessage($tg_id, $send, null, $InlineKeyboardMarkup);
    //     return;
    // }
    
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
