<?php


use app\models\TgCommunication;
use app\modules\purchase\models\PurchaseOrder;


function homeDelivery($bot, $from_id, $purchase_order_id, $address = null) {

    if ($address == null) {
        $tg_com = TgCommunication::findOne(['chat_id' => $from_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $from_id;
        $tg_com->to_chat_id = $from_id;
        $tg_com->from_whom = "homeDelivery_" . $purchase_order_id;
        $tg_com->save();

        $send = "В строке “Cообщение” укажите название района, населённого пункта и адрес доставки.";
        $bot->sendMessage($from_id, $send);
        return;
    }

    $purchase_order = PurchaseOrder::findOne($purchase_order_id);
    $purchase_order->address = $address;
    if ( ! $purchase_order->save() ) {
        $send = "Ошибка! Не смог сохранить адрес.";
        $bot->sendMessage($from_id, $send);
        return;
    }

    $send = "Заявка отправлена в обработку, БлагоДарим Вас за участие в “Общем деле”\r\n\r\n";
    $send .= "Оператор, обработки заказов с Вами свяжется перед доставкой";
    $bot->sendMessage($from_id, $send);

}