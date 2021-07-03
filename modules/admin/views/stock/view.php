<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;
use app\models\OrderStatus;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'История поставок';
$this->params['breadcrumbs'][]=['label'=>'Поставщики', 'url'=>URL::to(['/admin/provider'])];
$this->params['breadcrumbs'][] = $this->title;

$updateDepositUrl = Url::to(['/api/profile/admin/stock/update-deposit']);
$script = <<<JS
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
?>
<style>
    .grid-view th {
        white-space: normal;
    }
</style>
<div class="stock-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
               'attribute'=>'date',
                'value'=>function ($data){
                return Html::a(HTML::encode($data->date), URL::to(['viewbody', 'id'=>$data->id]));
                },
                'format'=>'raw',
            ],
            [
                'attribute' => 'provider_id',
                'label' => 'Поставщик',
                'content'=>function($data){
                    return $data->ProviderName;
                }
            ],

        ],
    ]); ?>

</div>