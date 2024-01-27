<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
use app\models\User;

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
            'attribute' => 'number',
            'content' => function ($model) {
                $id = null;
                $client = null;
                $user = User::findOne(['number' => $model->number]); 

                if ( ! $user ) return $model->number;

                $role = $user->role;
                if ($role == User::ROLE_MEMBER) {
                    $client = Member::findOne(['user_id' => $user->id]);
                }else if ($role == User::ROLE_PARTNER) {
                    $client = Partner::findOne(['user_id' => $user->id]);
                }else if ($role == User::ROLE_PROVIDER) {
                    $client = Provider::findOne(['user_id' => $user->id]);
                }
                
                if ( ! $client ) return $model->number;

                $id = $client->id;
                    
                $host = Yii::$app->params['host'];
                // $url = "$host/admin/$role/view?id=$id";
                $url = "$host/admin/$role?number=$user->number"; 

                return "<a href='$url'>$model->number</a>";
            }
        ]
    ],
    
    'layout'=>"{pager}\n{summary}\n{items}\n{pager}",
    
]); ?>

