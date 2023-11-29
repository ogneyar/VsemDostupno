<?php

use Yii;
use app\models\Product;
use app\modules\bots\api\Telegraph;


function getDescription($product_id) 
{    
    $product = Product::findOne($product_id);
    $description = $product->description;

    if (!$description) return null;

    $description = str_replace("<br />", "\r\n", $description);
    $description = str_replace("<br/>", "\r\n", $description);
    $description = str_replace("<br>", "\r\n", $description);
    $description = str_replace("&nbsp;", " ", $description);
    $description = str_replace("&mdash;", "—", $description);
    $description = str_replace("&ndash;", "-", $description);
    $description = str_replace("&ldquo;", "“", $description);
    $description = str_replace("&rdquo;", "”", $description);
    $description = str_replace("&laquo;", "«", $description);
    $description = str_replace("&raquo;", "»", $description);
    $description = preg_replace("(\<(/?[^>]+)>)", "", $description); // удаление HTML тегов

    $access_token = Yii::$app->params['access_token'];         
    $tGraph = new Telegraph($access_token);
    $content = '[{"tag":"p","children":["'.$description.'"]}]';
    if ($product->tgraph_path) {
        $data = $tGraph->editPage($product->tgraph_path, $product->name, $content);
    }else {
        $data = $tGraph->createPage($product->name, $content);
    }
    
    $product->tgraph_path = $data->path;
    $product->save();

    return $data->url;

}