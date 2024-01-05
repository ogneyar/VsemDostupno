<?php

// use Yii;
use DateTime;
use app\models\User;


function getPay($user) {

    $pay = "";

    if ( ($user->role == User::ROLE_MEMBER && $user->lastname == "lastname") || $user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN) 
    {
        $pay = "отсутствует";
    }
    else
    {
        $d = new DateTime();
        $date = $d->format('t.m.Y');
    
        if ($user->subscription->total > 0) $pay = "*Не внесён*";
        else $pay = "*Внесён до ".$date.".*";
    }

    return $pay;
}
?>