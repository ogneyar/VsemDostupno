<?php


use app\models\TgCommunication;


function search($bot, $chat_id) {

    $tg_com = TgCommunication::findOne(['chat_id' => $chat_id]);
    if ( ! $tg_com ) {
        $tg_com = new TgCommunication();
        $tg_com->chat_id = $chat_id;
        $tg_com->to_chat_id = $chat_id;
    }
    $tg_com->from_whom = "searchProducts";
    $tg_com->save();

    // $send = "Для быстрого поиска нужного Вам товара, в строке “Сообщение”, укажите ключевое слово и отправьте его.";     
    $send = "Укажите ключевое слово.";         
    $bot->sendMessage($chat_id, $send);
}