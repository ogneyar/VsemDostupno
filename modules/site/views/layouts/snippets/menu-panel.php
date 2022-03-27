<?php

use kartik\helpers\Html;

$constants = require(__DIR__ . '/../../../../../config/constants.php');
$web = $constants['WEB'];

?>
<div class="row">
    <div class="col-md-12">
        <?php 
        if (!$items && $data == 507) {
            echo Html::panel(
                [
                    'heading' => $heading,
                    'postBody' => Html::listGroup(
                        [
                            // [ // это пример содержимого одного элемента массива $items
                            //     'content' => 'загрузка страницы...',
                            //     'url' => $web . '/page/punkty-vydachi',
                            //     'options' => [ "target" => "_self" ],
                            //     'thumbUrl' => '/images/store/00/00/06/58.jpg?v=8f951e5389d5245fce0bf1707549b6b5',
                            //     'model' => [ 'id' => 65, 'photo_id' => 812, 'name' => '0', 'description' => 'description', 'root' => 24, 'left' => 16, 'right' => 23, 'level' => 3, 'slug' => "", 'purchase_timestamp' => "0000-00-00 00:00:00", 'order_timestamp' => "0000-00-00 00:00:00", 'visibility' => 1, 'order' => 0, 'for_reg' => 0, 'collapsed' => 1, 'parent' => 24, 'external_link' => "" ]
                            // ],
                        ],
                        [
                            'style' => $style
                        ]
                    ),
                    'headingTitle' => true,
                ],
                isset($type) ? $type : Html::TYPE_PRIMARY,
                [
                    'class' => 'menu-panel ' . (isset($class) ? $class : ''),
                    'style' => 'cursor: pointer;',
                    'data-cat' => $data,
                    'id' => 'menu-panel-507'
                ]
            );
        }else {
            echo Html::panel(
                [
                    'heading' => $heading,
                    'postBody' => Html::listGroup(
                        $items,
                        [
                            'style' => $style
                        ]
                    ),
                    'headingTitle' => true,
                ],
                isset($type) ? $type : Html::TYPE_PRIMARY,
                [
                    'class' => 'menu-panel ' . (isset($class) ? $class : ''),
                    'style' => 'cursor: pointer;',
                    'data-cat' => $data,
                ]
            );
        }
        ?>
    </div>
</div>

