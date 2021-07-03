<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title= "Перевод пая на лицевой счёт";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="purchase-index">
    <h1><?= Html::encode($this->title) ?></h1>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'purchase_date',
                    'header' => 'Дата доставки'
                ],
                [
                    'attribute' => 'balance_total',
                    'label' => 'Сумма П/В',
                ],
                [
                    'attribute' => 'balance_total',
                    'label'=>'Сумма на лицевом счёте',
                ],
            ],
        ]); ?>
</div>