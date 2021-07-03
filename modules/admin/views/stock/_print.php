<?php

use yii\helpers\Html;
use yii\grid\GridView;
?>
<div class="stock-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'product_id',
                'content' => function ($data){
                    return $data->ProductName;
                },
            ],
            'tare',
            'weight',
            'measurement',
            'count',
            'summ',
            'total_summ',
            [
                'header' => 'Зачислять на лицевой счёт',
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['checked' => $model->deposit, 'class' => 'deposit-check', 'onchange' => 'change_deposit(this)'];
                }
            ],
            'comment',
        ],
    ]); ?>
    

</div>