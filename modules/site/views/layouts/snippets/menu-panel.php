<?php

use kartik\helpers\Html;

?>
<div class="row">
    <div class="col-md-12">
        <?= Html::panel([
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
        ) ?>
    </div>
</div>
