<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;

$this->title= "Поступившие заказы";
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="purchase-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'purchase_date',
                'header' => 'Дата доставки',
                'headerOptions' => ['style' => 'min-width: 120px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'provider.name',
                'label' => 'Поставщик',
                'headerOptions' => ['style' => 'min-width: 150px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'productFeature.product.name',
                'label' => 'Наименование товара',
                'headerOptions' => ['style' => 'min-width: 150px; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'tare',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'weight',
                'headerOptions' => ['style' => 'min-width: 50px; word-break: break-all; vertical-align: top; font-weight: 600;']
            ],
            [
                'attribute' => 'measurement',
                'headerOptions' => ['style' => 'min-width: 50px; word-break: break-all; vertical-align: top; font-weight: 600;']
            ],
            [
                'header' => 'Заказано общее количество',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;'],
                'content' => function($data) {
                    return $data->orderedCount ? number_format($data->orderedCount) : 0;  
                }
            ],
            [
                'attribute' => 'summ',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;']
            ],
            [
                'header' => 'На общую сумму',
                'headerOptions' => ['style' => 'vertical-align: top; font-weight: 600;'],
                'attribute' => 'orderedTotal',  
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
                                        'url' => Url::to(['/purchase/provider/contibute']),
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