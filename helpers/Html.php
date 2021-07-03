<?php

namespace app\helpers;

use Yii;

class Html extends \kartik\helpers\Html
{
    static public function makeTitle($text)
    {
        $words = preg_split('/\s/', mb_strtolower($text, Yii::$app->charset));

        if ($words) {
            $words[0] = mb_convert_case($words[0], MB_CASE_TITLE, Yii::$app->charset);
            $text = implode(' ', $words);
        }

        return $text;
    }
}
