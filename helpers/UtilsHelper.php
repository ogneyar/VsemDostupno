<?php
namespace app\helpers;

class UtilsHelper {
    public static function cutStr($str, $length = 50, $postfix = '...')
    {
        if (strlen($str) <= $length)
            return $str;
     
        $temp = substr($str, 0, $length);
        return substr($temp, 0, strrpos($temp, ' ') ) . $postfix;
     }
}