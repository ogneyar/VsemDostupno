<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\jui\AutoComplete;

/* @var $this yii\web\View */
/* @var $model app\models\StockHead */

$this->title = 'Старые коллективные закупки';
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['/admin/provider']];
$this->params['breadcrumbs'][] = $this->title;

// $config = require(__DIR__ . '/../../../../../config/urlManager.php');
// $baseUrl = $config['baseUrl'];


$script = <<<JS
    $(function () {
        $('#purchase-accept-product-modal').on('shown.bs.modal', function (e) {
            $("#product-id").val('0');
            $("#purchase-product-form").html('');
        });
        $(".avail-product").click(function() {
            console.log('21');
        });
    })
JS;
$this->registerJs($script, $this::POS_END);
?>
<div class="stock-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::button('Добавить товар', ['class' => 'btn btn-success', 'data-toggle' => 'modal', 'data-target' => '#purchase-accept-product-modal']); ?>
    
    <?= Html::a('Новые закупки', "create", ['class' => 'btn btn-success']); ?>
    
</div>

<div class="">
    <p>
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'stop_date',
                    'format' => ['date', 'dd-MM-y'],
                ],
                [
                    'attribute' => 'purchase_date',
                    'format' => ['date', 'dd-MM-y'],
                ],
                [
                    'attribute' => 'product_feature_id',
                    'content' => function($data) {
                        return $data->productName;
                    },
                ],
                'tare',
                'weight',
                'measurement',
                'summ',
                'purchase_total',
                [
                    'header' => 'Автопродление',
                    'class' => 'yii\grid\CheckboxColumn',
                    'checkboxOptions' => function($model, $key, $index, $column) {
                        if ($model->renewal) {
                            return ['checked' => true, 'class' => 'deposit-check', 'onchange' => 'change_renewal(this)'];
                        }else {
                            return ['class' => 'deposit-check', 'onchange' => 'change_renewal(this)'];
                        }
                    }
                ],
                [
                    'label' => 'Удаление',
                    'format' => 'raw',
                    'value' => function($data){
                        return Html::a(
                            'Удалить',
                            "delete-old-data?id=".$data->id."&page=".$_GET['page'],
                            [
                                'title' => 'Смелей вперед!',
                                'class' => 'btn btn-danger'
                            ]
                        );
                    }
                ],
            ],
        ]); ?>
    </p>
</div>

