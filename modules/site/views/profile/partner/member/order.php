<?php

use yii\helpers\Url;
use yii\grid\GridView;
use kartik\helpers\Html;

/* @var $this yii\web\View */
$this->title = $title;
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="order-index">
    <p>
        <?= Html::a('Добавить заказ', Url::to(['profile/partner/member/order-create']), ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'order_id',
                'content' => function($model) {
                    return sprintf("%'.05d\n", $model->order_id);
                },
            ],
            'created_at',
            'htmlFormattedInformation:raw',
        ],
    ]); ?>
</div>
