<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;
use app\models\OrderStatus;
use app\models\StockHead;
use app\models\StockBody;
use app\models\ProviderStock;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */



$head = StockHead::find()->where('id=:id',[':id'=>$_GET['id']])->one();
$body = new StockBody;
$this->title = 'Поставка от ' . $head->date;
$this->params['breadcrumbs'][] = ['label'=>'Поставщики', 'url'=>URL::to(['/admin/provider'])];
$this->params['breadcrumbs'][] = ['label'=>'История поставок', 'url'=>URL::to(['/admin/stock/view?id='.$head->provider_id])];
$this->params['breadcrumbs'][] = $this->title;
$updateDepositUrl = Url::to(['/api/profile/admin/stock/update-deposit']);
/*$script = <<<JS
$(function () {
    $('input[type="checkbox"][class="update-deposit"]').on('change', function () {
        $.ajax({
            url: '$updateDepositUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-product-id'),
                visibility: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    alert('Ошибка обновления видимости товара');
                }
            },
            error: function () {
                alert('Ошибка обновления видимости товара');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
*/?>
<style>
    .grid-view th {
        white-space: normal;
    }
    pre {
        margin: 0;
        padding: 0;
        border:0;
        font-size: 14px;
        font-style: normal;
        background: #ffffff;
        font-weight: bold;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        display: inline-flex;
    }
    .dropdown{
        float:right;
    }

</style>
<div class="stock-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <div class="dropdown">
        <button data-toggle="dropdown" class="btn btn-default">Скачать <b class="caret"></b></button>
        <?php
        echo DropdownX::widget([
            'items'=>[
                [
                    'label' => 'Акт на передачу прав',
                    'url' => Url::to(['provider/download-transfer-of-rights', 'id' => $head->id]),
                ],
                [
                    'label' => 'Карточка учета материалов',
                    'url' => Url::to(['provider/download-m-17', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'М-15 (Накладная)',
                    'url' => Url::to(['provider/download-m-15', 'id' => $head->id]),
                ],
                [
                    'label' => 'Расходная накладная',
                    'url' => Url::to(['provider/download-sales-invoce', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'Товарно-транспортная',
                    'url' => Url::to(['provider/download-consignment-note', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'ТОРГ-12 (Товарная)',
                    'url' => Url::to(['provider/download-torg-12', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'Транспортная накладная',
                    'url' => Url::to(['provider/download-waybill', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'УПД (статус 2)',
                    'url' => Url::to(['provider/download-upd', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'Договор поставки товара №1',
                    'url' => Url::to(['provider/download-agreement-delivery-1', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'Договор поставки товара №2',
                    'url' => Url::to(['provider/download-agreement-delivery-2', 'id' => $head->provider_id]),
                ],
                [
                    'label' => 'Акт приёмки паевого взноса',
                    'url' => Url::to(['provider/download-acceptance-fee-act', 'id' => $head->id]),
                ],
                /*[
                    'label' => 'Договор поставки товара №3',
                    'url' => Url::to(['provider/download-agreement-delivery-3', 'id' => $head->provider_id]),
                ],*/
            ]
        ]);
        ?>
    </div>
    <div class="dropdown">
        <button data-toggle="dropdown" class="btn btn-default">Действия <b class="caret"></b></button>
        <?php
            echo DropdownX::widget([
                'items' => [
                    [
                        'label' => 'Удалить',
                        'url' => Url::to(['/admin/stock/delete', 'id' => $head->id, 'provider' => $head->provider_id]),
                        'linkOptions' => [
                            'data' => [
                                'confirm' => 'Вы уверены что хотите удалить данную поставку?',
                                'method' => 'post',
                            ],
                        ]
                    ],
                ],
            ]);
        ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'caption'=>'<b style="color:black;">'.$head->who .'<pre>    Дата приемки '.$head->date. '</pre>' .'<br> '. $head->ProviderName.'</b>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'product_id',
                'content'=>function ($data){
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
                'header' => $body->getAttributeLabel('deposit'),
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['checked' => $model->deposit, 'class' => 'deposit-check', 'disabled' => ProviderStock::isSolded($model->id)];
                }
            ],
            'comment',

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
                                        'label' => 'Удалить поставку',
                                        'url' => Url::to(['/admin/stock/delete-body', 'id' => $model->id, 'provider' => $model->stockHead->provider_id]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => 'Вы уверены что хотите удалить данную поставку?',
                                                'method' => 'post',
                                            ],
                                        ], 
                                    ],
                                    [
                                        'label' => 'Удалить вид товара',
                                        'url' => Url::to(['/admin/stock/delete-feature', 'id' => $model->id, 'provider' => $model->stockHead->provider_id]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => 'Вы уверены что хотите удалить этот вид товара?',
                                                'method' => 'post',
                                            ],
                                        ], 
                                    ],
                                ],
                            ]);
                    }
                ],
            ],
        ],
    ]); ?>
</div>