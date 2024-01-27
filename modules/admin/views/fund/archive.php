<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Архив';
$this->params['breadcrumbs'][] = ['label' => 'Фонды', 'url' => ['/admin/fund']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="fund-distribute">
    <h1><?= Html::encode($this->title) ?></h1>
</div>

<?php
// if ($archive) {
//     foreach($archive as $arch) {
//         echo $arch->operation;
//         echo $arch->amount;
//     }
// }
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-bordered',
    ],
    'columns' => [
        // [
        //     'label' => '№',
        //     'attribute' => 'id',
        //     'headerOptions' => ['style' => 'min-width: 55%;']
        // ],
        [
            'label' => 'Дата/время',
            'attribute' => 'date',
            'headerOptions' => ['style' => 'min-width: 55%;'],
            // 'format' =>  ['date', 'php:Y-m-d H:i:s'],
            'format' =>  ['date', 'php:d.m.Yг. в Hч.iм.'],
            // 'content' => function($data) {
            //     return "value";
            // }
        ],
        [
            'label' => 'Указать операцию',
            'attribute' => 'operation',
            'headerOptions' => ['style' => 'min-width: 55%;']
        ],
        [
            'label' => 'Наименование счёта',
            'attribute' => 'account_name'
        ],
        [
            'label' => 'Сумма',
            'attribute' => 'amount'
        ],
        [
            'label' => 'Основание / Причина',
            'attribute' => 'reason'
        ],
        [
            'label' => 'Ф.И.О.',
            'attribute' => 'fio'
        ],
        [
            'label' => 'Номер регистрации',
            'attribute' => 'number'
        ]
    ],
    
    'layout'=>"{pager}\n{summary}\n{items}\n{pager}",
    
]); ?>

