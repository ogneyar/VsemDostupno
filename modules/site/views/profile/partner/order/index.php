<?php
use kartik\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\helpers\NumberColumn;
use app\models\ProductFeature;

$this->title = 'Заказы на склад';
$this->params['breadcrumbs'] = [$this->title];
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>
<h4>Сбор заявок за <?= date('d.m.Y', strtotime($date['end'])); ?></h4>
<div class="order-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn', 
                'header' => '№ п/п', 
                'footer' => 'ИТОГО:',
                'footerOptions' => ['colspan' => 4]
            ],
            [
                'label' => 'Наименование товаров', 
                'value' => function ($data) {
                    return $data['product_name'] . ', ' . ProductFeature::getFeatureNameById($data['product_feature_id']);
                },
                'footerOptions' => ['style' => 'display: none;']
            ],
            [
                'label' => 'Поставщик',
                'value' => function ($data) {
                    return $data['provider_name'];
                },
                'footerOptions' => ['style' => 'display: none;']
            ],
            [
                'label' => 'Количество',
                'format' => 'raw',
                'contentOptions' => ['style' => 'font-weight: 600;'],
                'value' => function ($data) use ($date) {
                    return Html::a(ProductFeature::isWeights($data['product_feature_id']) ? $data['quantity'] : number_format($data['quantity']), Url::to(['/profile/partner/order/detail', 'id' => $data['product_feature_id'], 'prid' => $data['provider_id'], 'date' => date('Y-m-d', strtotime($date['end']))]), ['style' => 'text-decoration: underline;']);
                },
                'footerOptions' => ['style' => 'display: none;']
            ],
            [
                'label' => 'На сумму',
                'contentOptions' => ['style' => 'font-weight: 600;'],
                'value' => function ($data) {
                    return $data['total'];
                },
                'class' => NumberColumn::className(),
                'footerOptions' => ['style' => 'font-weight: 600;'],
            ]
        ],
        'showFooter' => true,
    ]);?>
</div>