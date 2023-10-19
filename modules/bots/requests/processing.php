<?php

// include './message.php';
require __DIR__ . '/message.php';
// include './callbackQuery.php';
require __DIR__ . '/callbackQuery.php';


function requestProcessing($bot, $master, $admin) {
    $data = $bot->data;

    if (isset($data['message'])) {
        requestMessage($bot, $data['message'], $master, $admin);
    }else if (isset($data['callback_query'])) {
        requestCallbackQuery($bot, $data['callback_query'], $master, $admin);
    }    
}

