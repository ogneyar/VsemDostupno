<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
//* @var $dataProvider yii\data\ActiveDataProvider */
$this->title= "Мои товары";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute'=>'stock_body.stockHead.date',
                'headerOptions' => ['style' => 'min-width: 120px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute'=>'stock_body.stockHead.provider.name',
                'label'=>'Поставщик',
                'headerOptions' => ['style' => 'min-width: 150px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute'=>'stock_body.product.name',
                'label'=>'Наименование товара',
                'headerOptions' => ['style' => 'min-width: 150px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'stock_body.tare',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'stock_body.weight',
                'headerOptions' => ['style' => 'min-width: 50px; word-break: break-all; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'stock_body.measurement',
                'headerOptions' => ['style' => 'min-width: 50px; word-break: break-all; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'total_rent',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'stock_body.summ',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'total_sum',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'reaminder_rent',
                'label' => 'Кол-во на остатке',
                'headerOptions' => ['style' => 'min-width: 50px; word-break: break-all; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'summ_reminder',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template'=> '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {


                        return Html::beginTag('div', ['class' => 'dropdown']) .
                            Html::button('Действия <span class="caret"></span>', [
                                'type' => 'button',
                                'class' => 'btn btn-default',
                                'data-toggle' => 'dropdown'
                            ]) .

                            DropdownX::widget([
                                'items' => [
                                    [
                                        'label' => 'Зачислено на Л/С',
                                        'url' => Url::to(['/site/stock/contibute']),
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => Url::to(['/site/stock/delete', 'id' => $model->id]),
                                    ],
                                ],
                            ]);
                    }
                ],
            ],
        ],

    ]);
    ?>

</div>
