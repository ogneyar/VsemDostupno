<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\jui\AutoComplete;
use app\models\StockHead;

/* @var $this yii\web\View */
/* @var $model app\models\StockHead */

$this->title = 'Принять товар';
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['/admin/provider']];
$this->params['breadcrumbs'][] = $this->title;

$listdata = StockHead::find()
    ->select(['who as value', 'who as label'])
    ->groupBy('who')
    ->asArray()
    ->all();

$script = <<<JS
    $(function () {
        $('#accept-product-modal').on('shown.bs.modal', function (e) {
            $("#product-id").val('0');
            $("#stockhead-product-form").html('');
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

    <?= Html::button('Принять товар', ['class' => 'btn btn-success', 'data-toggle' => 'modal', 'data-target' => '#accept-product-modal']); ?>
    
</div>
<div class="stock-list" id="stock-list">
</div>

<?php Modal::begin([
    'id' => 'accept-product-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Приемка товара' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . 'Закрыть' . '</a>
                 <button id="accept-product-btn" class="btn btn-success" type="button" onclick="acceptProduct()">' . 'Принять' . '</button>',
]); ?>
    
    <?php $form = ActiveForm::begin(['id' => 'addProd']); ?>
        
        <?= $form->field($model, 'who')->widget(AutoComplete::className(), [
            'clientOptions' => [
                'source' => $listdata,
            ],
            'options'=>[
                'class'=>'form-control'
            ]
        ]) ?>
        
        <?= $form->field($model, 'date')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_COMPONENT_APPEND,
            'readonly' => true,
            'layout' => '{input}{picker}',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ]) ?>
        
        <div class="form-group field-stockhead-provider_id required">
            <label class="control-label" for="provider_id">ФИО или наименование организации поставщика</label>
            <?= Select2::widget([
                'id' => 'stockhead-provider_id',
                'name' => 'StockHead[provider_id]',
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
                    'select2:select' => new JsExpression('function() { toggleProductsContainer("show"); }'),
                    'select2:unselect' => new JsExpression('function() { toggleProductsContainer("hide") }'),
                ],
            ]); ?>
        </div>
        
        <h3>Товары</h3>

        <div class="stockbody-1">
            <div class="form-group field-stockhead-product" id="stockhead-product-container" style="display: none;"></div>
	        <div class="form-group field-stockhead-product" id="stockhead-product-form"></div>	
        </div>
    <?php ActiveForm::end(); ?>
<?php Modal::end(); ?>
