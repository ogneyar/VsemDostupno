<?php

use DateTime;
use app\models\User;
use app\models\Account;


function getBalanceByNumber($bot, $chat_id, $number, $additional_text = null) {
    
    $user = User::findOne(['number' => $number, 'disabled' => 0]);

    if (!$user) {
        $bot->sendMessage($chat_id, "Нет найденного пользователя с таким регистрационным номером.");
        return;
    }

    $account = Account::findOne(['id' => $user->id]);
    
    $face = $user->getAccount(Account::TYPE_DEPOSIT); // расчётный (лицевой) счёт
    $invest = $user->getAccount(Account::TYPE_BONUS); // инвестиционный счёт
    $partner = $user->getAccount(Account::TYPE_STORAGE); // партнёрский счёт
    $pay = $user->getAccount(Account::TYPE_SUBSCRIPTION); // членский взнос
    
    

    $send = "*".$user->firstname." ".$user->patronymic."!*\r\n\r\n";

    $send .= "Выписка по счету.\r\n";

    if ($user->role == User::ROLE_MEMBER) {         
        if ($user->lastname == "lastname") { // пройдена упрощённая регистрация   
            $send .= "*Не зарегистрированный участник:*\r\n";
            $send .= "*Рег.№ $number*\r\n";
        }else {
            $send .= "*Пайщик - Участник: Рег.№ $number*\r\n";
        }
    }
    else
    if ($user->role == User::ROLE_PARTNER) {
        $send .= "*Пайщик - Партнёр: Рег.№ $number*\r\n";
    }
    else
    if ($user->role == User::ROLE_PROVIDER) {           
        $send .= "*Пайщик - Поставщик: Рег.№ $number*\r\n";
    }

    if ($additional_text) $send .= $additional_text . "\r\n";

    $send .= "Лицевой счёт:\r\n    ".formatPrice($face->total)."\r\n";
    $send .= "Инвестиционный счёт:\r\n    ".formatPrice($invest->total);

    if ($user->role == User::ROLE_MEMBER) {
        if ($user->lastname == "lastname") $send .= "\r\n*Накопительный счёт не задействован.*";
    }

    $send .= "\r\n";

    if ($user->role == User::ROLE_PARTNER) {
        $send .= "Партнёрский счёт:\r\n    ".formatPrice($partner->total)."\r\n";
    }
    
    if ( ! ($user->role == User::ROLE_MEMBER && $user->lastname == "lastname")) {
        $send .= "Ежемесячный паевой взнос: \r\n";
            
        $d = new DateTime();
        $date = $d->format('t.m.Y');

        if ($pay->total > 0) $send .= "*Не внесён*";
        else $send .= "*Внесён до ".$date.".*";
    }


    $bot->sendMessage($chat_id, $send, "markdown");

    return;
}