<?php

// use Yii;
use app\models\User;


function getRole($user) {
    $send = "";
    
    if ($user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN) {
        $send = "Администратор";
    }
    else
    if ($user->role == User::ROLE_MEMBER) {         
        if ($user->lastname == "lastname") { // пройдена упрощённая регистрация   
            $send .= "Не зарегистрированный участник";
        }else {
            $send .= "Пайщик - Участник";
        }
    }
    else
    if ($user->role == User::ROLE_PARTNER) {
        $send .= "Пайщик - Партнёр";
    }
    else
    if ($user->role == User::ROLE_PROVIDER) {           
        $send .= "Пайщик - Поставщик";
    }

    return $send;
}

?>