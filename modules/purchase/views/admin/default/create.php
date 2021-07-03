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

$this->title = 'Оформление коллективной закупки';
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['/admin/provider']];
$this->params['breadcrumbs'][] = $this->title;

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
    
</div>

<div class="">
    <p>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'purchase_date',
                    'format' => ['date', 'dd-MM-y'],
                ],
                [
                    'attribute' => 'stop_date',
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
                        return ['checked' => $model->renewal, 'class' => 'deposit-check', 'onchange' => 'change_renewal(this)'];
                    }
                ],
            ],
        ]); ?>
    </p>
</div>

<?php Modal::begin([
    'id' => 'purchase-accept-product-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Добавить товар для закупки' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . 'Закрыть' . '</a>
                 <button id="purchase-accept-product-btn" class="btn btn-success" type="button" onclick="acceptProductPurchase()">' . 'Добавить' . '</button>',
]); ?>
    
    <?php $form = ActiveForm::begin(['id' => 'addProdPurchase']); ?>
        
        <div class="form-group field-purchase-created_date required">
            <label class="control-label">Дата оформления</label>
            <?= DatePicker::widget([
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'name' => 'PurchaseProduct[created_date]',
                'id' => 'created-date',
                'value' => date('Y-m-d'),
                'layout' => '{input}{picker}',
                'readonly' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]); ?>
        </div>
        
        <div class="form-group field-purchase-purchase_date required">
            <label class="control-label">Дата закупки</label>
            <?= DatePicker::widget([
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'name' => 'PurchaseProduct[purchase_date]',
                'id' => 'purchase-date',
                'layout' => '{input}{picker}',
                'readonly' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]); ?>
        </div>
        
        <div class="form-group field-purchase-stop_date required">
            <label class="control-label">Дата Стоп заказа</label>
            <?= DatePicker::widget([
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'name' => 'PurchaseProduct[stop_date]',
                'id' => 'stop-date',
                'layout' => '{input}{picker}',
                'readonly' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]); ?>
        </div>
        
        <div class="form-group">
            <?= Html::checkbox('PurchaseProduct[renewal]', false, ['id' => 'renewal']); ?>
            <label for="renewal">Автопродление</label>
        </div>
        
        <div class="form-group required">
            <label class="control-label" for="purchase_total">Сумма заказа</label>
            <?= Html::textInput('PurchaseProduct[purchase_total]', null, ['class' => 'form-control', 'id' => 'purchase_total']); ?>
        </div>
        
        <div class="form-group field-purchase-provider_id required">
            <label class="control-label" for="provider_id">ФИО или наименование организации поставщика</label>
            <?= Select2::widget([
                'id' => 'purchase-provider_id',
                'name' => 'PurchaseProduct[provider_id]',
                'options' => ['placeholder' => 'Введите ФИО или наименование организации поставщика'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 1,
                    'language' => substr(Yii::$app->language, 0, 2),
                    'ajax' => [
                        'url' => Url::to(['/api/profile/admin/provider/id-search']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(user) { return user.text; }'),
                    'templateSelection' => new JsExpression('function (user) { return user.text; }'),
                ],
                'pluginEvents' => [
                    'select2:select' => new JsExpression('function() { togglePurchaseProductsContainer("show"); }'),
                    'select2:unselect' => new JsExpression('function() { togglePurchaseProductsContainer("hide") }'),
                ],
            ]); ?>
        </div>
        
        <h3>Товары</h3>

        <div class="purchase-products-1">
            <div class="form-group field-purchase-product" id="purchase-product-container" style="display: none;"></div>
            <div class="form-group field-purchase-product" id="purchase-product-form"></div>    
        </div>
    <?php ActiveForm::end(); ?>
<?php Modal::end(); ?>
