<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use yii\bootstrap\Modal;
use mihaildev\ckeditor\CKEditor;
use app\models\Category;
use app\models\Product;
use app\models\FundProduct;
use app\models\CandidateGroup;
use wbraganca\fancytree\FancytreeWidget;
use kartik\file\FileInput;
use kartik\date\DatePicker;
use kartik\select2\Select2;

use app\modules\mailing\models\MailingProduct;

// const PERCENT_FOR_ALL = 25;
$constants = require(__DIR__ . '/../../../../config/constants.php');

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("CKEDITOR.plugins.addExternal('youtube', '/ckeditor/plugins/youtube/youtube/plugin.js', '');");


?>

<div class="product-form">
    <?php if (Yii::$app->hasModule('mailing')): ?>
        <?php $mailing_product_id = $model->isNewRecord ? Product::getNextId() : $model->id ?>
        <?php if (!MailingProduct::find()->where(['product_id' => $mailing_product_id])->exists()): ?>
            <?php $this->registerJsFile('/js/mailing/admin.js',  ['position' => $this::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]); ?>
            <?php $groups = CandidateGroup::find()->all(); ?>
            <?= Html::button('Рассылка информации', ['class' => 'btn btn-success', 'id' => 'mailing-product-btn', 'data-toggle' => 'modal', 'data-target' => '#mailing-product-modal']); ?>
            <?php Modal::begin([
                'id' => 'mailing-product-modal',
                'options' => ['tabindex' => false,],
                'header' => '<h4>' . 'Рассылка информации' . '</h4>',
                'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true" id="mailing-product-info-cancel-btn">' . 'Отменить' . '</a>
                             <button id="mailing-product-info-save-btn" class="btn btn-success" type="button">' . 'Сохранить' . '</button>',
            ]); ?>

                <?php $form = ActiveForm::begin(['id' => 'mailing_product_info_frm']); ?>
            
                    <input type="hidden" name="candidates-all" id="candidates-all-hdn" value="0">
                    <input type="hidden" name="product-id" id="product-id-hdn" value="<?= $mailing_product_id; ?>">
                    <div class="form-group">
                        <?= Html::checkbox('members', false, ['id' => 'members']); ?>
                        <label for="members">Для Участников</label>
                    
                        <?= Html::checkbox('providers', false, ['id' => 'providers']); ?>
                        <label for="providers">Для Поставщиков</label>
                    
                        <?= Html::checkbox('candidates', false, ['id' => 'candidates']); ?>
                        <label for="candidates">Для Кандидатов</label>
                    </div>
            
                    <div class="form-group" id="candidates-groups" style="display: none;">
                        <?php if ($groups): ?>
                            <?= Html::checkbox('candidates-all', false, ['id' => 'candidates-all']); ?>
                            <label for="candidates-all">Все</label>&nbsp;&nbsp;&nbsp;
                            <?php foreach ($groups as $group): ?>
                                <?= Html::checkbox('candidates[' . $group->id . ']', false, ['id' => 'candidates-' . $group->id, 'class' => 'candidates-gr']); ?>
                                <label for="candidates-<?= $group->id; ?>"><?= $group->name; ?></label>&nbsp;&nbsp;&nbsp;
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
            
                    <div class="form-group">
                        <label>Информационная категория</label>
                    </div>
            
                    <div class="form-group">
                        <?= Html::radio('category', true, ['value' => '2', 'label' => 'Реклама новых товаров']); ?>
                        <?= Html::radio('category', false, ['value' => '3', 'label' => 'Акции, спец предложения']); ?>
                        <?= Html::radio('category', false, ['value' => '4', 'label' => 'Информация о предстоящих закупках']); ?>
                    </div>
            
                    <div class="form-group">
                        <label for="subject">Тема</label>
                        <?= Html::textInput('subject', null, ['class' => 'form-control', 'id' => 'subject']); ?>
                    </div>
            
                    <div class="form-group" id="message-container">
                        <label for="subject">Сообщение</label>
                        <?= CKEditor::widget([
                            'name' => 'message',
                            'id' => 'message',
                            'value' => '<br>На это письмо отвечать не нужно, рассылка произведена автоматически.',
                            'editorOptions' => [
                                'preset' => 'basic',
                                'inline' => false,
                            ]
                        ]);?>
                    </div>
            
                <?php ActiveForm::end(); ?>

            <?php Modal::end(); ?>
        <?php endif; ?>
    <?php endif; ?>
    <br><br>
    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>

    <?= $form->field($model, 'visibility')->checkbox() ?>

    <?= $form->field($model, 'only_member_purchase')->checkbox() ?>
    
    <?= $form->field($model, 'auto_send')->checkbox(); ?>
    
    <?php if ($model->isNewRecord): ?>
        <?php if (!empty($model->provider_id)): ?>
            <input type="hidden" name="Product[provider_id]" value="<?= $model->provider_id; ?>">
            <div class="form-group field-provider-name required">
                <label class="control-label" for="provider-name">Название организации / ФИО поставщика</label>
                <input id="provider-name" class="form-control" name="provider_name" value="<?= $provider->name . ' / ' . $provider->user->fullName; ?>" readonly="" type="text">
            </div>
            <label for="product-category-id" class="control-label">Категория</label>
            <select id="product-category-id" class="form-control" name="Product[category_id]">
                <option value="0" selected disabled>Выберите категорию товара</option>
                <?php foreach ($categories as $cat):?>
                    <?php if (!count($cat->category->getAllChildrenQuery()->all())): ?>
                        <option value="<?= $cat->category->id; ?>"><?= $cat->category->name; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <div class="form-group field-product-provider_id required">
                <label class="control-label" for="provider_id">ФИО или наименование организации поставщика</label>
                <?= Select2::widget([
                    'id' => 'product-provider_id',
                    'name' => 'Product[provider_id]',
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
                        'select2:select' => 'function() { 
                            var html = $.ajax({
                                url: "/web/admin/product/get-categories",
                                async: false,
                                type: "POST",
                                data: {provider_id: $("#product-provider_id").val()}
                            }).responseText;
                            if (html) {
                                $("#product-categories-container").html(html);
                            }
                            $("#product-categories-container").show();
                        }',
                        'select2:unselect' => 'function() { 
                            $("#product-categories-container").hide();
                        }',
                    ],
                ]) ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($model->provider)): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="product-change-provider" name="change_provider">
                    Изменить поставщика
                </label>
            </div>
            
            <div id="product-provider-exists-container">
                <input type="hidden" name="Product[provider_id]" value="<?= $model->provider->id; ?>">
                <input type="hidden" name="Product[category_id]" value="<?= $model->category->id; ?>">
                <div class="form-group field-provider-name required">
                    <label class="control-label" for="provider-name">Название организации / ФИО поставщика</label>
                    <input id="provider-name" class="form-control" name="provider_name" value="<?= $model->provider->name . ' / ' . $model->provider->user->fullName; ?>" readonly="" type="text">
                </div>
                <div class="form-group field-category-name required">
                    <label class="control-label" for="category-name">Название категории</label>
                    <input id="category-name" class="form-control" name="category_name" value="<?= $model->category->name; ?>" readonly="" type="text">
                </div>
            </div>
            <div id="product-provider-change-container" style="display: none;">
                <div class="form-group field-product-provider_id required">
                <label class="control-label" for="provider_id">ФИО или наименование организации поставщика</label>
                <?= Select2::widget([
                    'id' => 'product-provider_id',
                    'name' => 'Product[provider_id_new]',
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
                        'select2:select' => 'function() { 
                            var html = $.ajax({
                                url: "/web/admin/product/get-categories",
                                async: false,
                                type: "POST",
                                data: {provider_id: $("#product-provider_id").val()}
                            }).responseText;
                            if (html) {
                                $("#product-categories-container").html(html);
                            }
                            $("#product-categories-container").show();
                        }',
                        'select2:unselect' => 'function() { 
                            $("#product-categories-container").hide();
                        }',
                    ],
                ]) ?>
            </div>
            </div>
        <?php else: ?>
            <div class="form-group field-product-provider_id required">
                <label class="control-label" for="provider_id">ФИО или наименование организации поставщика</label>
                <?= Select2::widget([
                    'id' => 'product-provider_id',
                    'name' => 'Product[provider_id]',
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
                        'select2:select' => 'function() { 
                            var html = $.ajax({
                                url: "/web/admin/product/get-categories",
                                async: false,
                                type: "POST",
                                data: {provider_id: $("#product-provider_id").val()}
                            }).responseText;
                            if (html) {
                                $("#product-categories-container").html(html);
                            }
                            $("#product-categories-container").show();
                        }',
                        'select2:unselect' => 'function() { 
                            $("#product-categories-container").hide();
                        }',
                    ],
                ]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="form-group field-product-category required" style="display: none;" id="product-categories-container">
        
    </div>

    <?= $form->field($model, 'name') ?>

    <?php if (!$model->isNewRecord): ?>
        <div class="form-group">
            <?php if ($model->productFeatures): ?>
                <div><label class="control-label">Имеющиеся виды</label></div>
                <?php foreach ($model->productFeatures as $feat): ?>
                    <div class="product-card-feature">
                        <div class="product-card-description">
                            <?php if ($feat->is_weights == 1): ?>
                                <?= '<b>Разновес</b> в <b>' . $feat->tare . '</b> по <b>' . $feat->volume . ' ' . $feat->measurement . '</b> общим количеством <b>' . $feat->quantity . ' ' . $feat->measurement . '</b> по цене <b>' . $feat->productPrices[0]->purchase_price . ' руб. </b> за ' . $feat->measurement ?>
                            <?php else: ?>
                                <?= '<b>' . $feat->tare . ', ' . $feat->volume . ' ' . $feat->measurement . '</b> в количестве <b>' . $feat->quantity . '</b> шт., закупочная цена - <b>' . $feat->productPrices[0]->purchase_price . '</b> руб., цена для участников - <b><span data-f-m-id="' . $feat->id . '">' . $feat->productPrices[0]->member_price . '</span></b> руб., цена для всех - <b><span data-f-a-id="' . $feat->id . '">' . $feat->productPrices[0]->price . '</span></b> руб.'; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (count($model_fund) > 0): ?>
                            <div class="product-card-price-btns">
                                <button type="button" class="btn btn-primary btn-xs update-price-modal" data-id="<?= $feat->id; ?>" data-toggle="modal" data-target="#update-price-modal">Редактировать цену</button>
                                <a href="<?= Url::to(['/admin/product/delete-feature', 'id' => $feat->id, 'product' => $model->id]); ?>" title="Удалить" data-pjax="0" data-method="post" data-confirm="Вы уверены что хотите удалить этот вид товара?"><span class="glyphicon glyphicon-trash"></span></a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?= $form->field($model, 'expiry_timestamp')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
        'readonly' => true,
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            'startDate' => (new \DateTime('now'))->format('Y-m-d'),
        ],
    ]) ?>

    <?= $form->field($model, 'composition')->textArea(['rows' => '6']) ?>

    <?= $form->field($model, 'packing') ?>

    <?= $form->field($model, 'manufacturer') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'description')->widget(CKEditor::className(), [
        'editorOptions' => [
            'extraPlugins' => 'youtube',
            'preset' => 'full',
            'inline' => false,
        ],
    ]) ?>

    <?php
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($model->productHasPhoto as $item) {
            $initialPreview[] = Html::img($item->thumbUrl);
            $initialPreviewConfig[] = [
                'url' => Url::to(['/api/profile/admin/photo/delete']),
                'extra' => [
                    'PhotoDeletion[key]' => $item->photo->id,
                    'PhotoDeletion[id]' => $model->id,
                    'PhotoDeletion[class]' => $model->className(),
                ],
            ];
        }
        echo $form->field($model, 'gallery[]')->widget(FileInput::className(), [
            'name' => get_class($model) . '[gallery[]]',
            'language' => substr(Yii::$app->language, 0, 2),
            'options' => [
                'multiple' => true,
            ],
            'pluginOptions' =>[
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'previewFileType' => 'any',
                'maxFileCount' => Product::MAX_FILE_COUNT,
            ]
        ]);
    ?>
    
    <?php
        $initialPreview = [];
        $initialPreviewConfig = [];
        if ($model->photo) {
            $initialPreview[] = Html::img($model->thumbUrlManufacturer);
            $initialPreviewConfig[] = [
                'url' => Url::to(['/api/profile/admin/photo/delete']),
                'extra' => [
                    'PhotoDeletion[key]' => $model->photo->id,
                    'PhotoDeletion[id]' => $model->id,
                    'PhotoDeletion[class]' => $model->className(),
                    'PhotoDeletion[manufacturer]' => 1,
                ],
            ];
        }
        echo $form->field($model, 'photo')->widget(FileInput::className(), [
            'name' => get_class($model) . '[photo]',
            'language' => substr(Yii::$app->language, 0, 2),
            'options' => [
                'multiple' => false,
            ],
            'pluginOptions' =>[
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'previewFileType' => 'any',
            ]
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php if (!$model->isNewRecord): ?>
    <?php Modal::begin([
        'id' => 'update-price-modal',
        'options' => ['tabindex' => false,],
        //'size' => Modal::SIZE_SMALL,
        'header' => '<h4>' . 'Фонды' . '</h4>',
        'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . 'Закрыть' . '</a>
                     <button id="update-price-btn" class="btn btn-success" type="button" onclick="updatePrice()">' . 'Сохранить' . '</button>',
    ]); ?>

        <?php if (count($model_fund) > 0): ?>
            <?php foreach ($model_fund as $fund): ?>
                <div class="has-feedback" style="height: 35px;">
                    <span><?= $fund->name; ?></span>
                    <input type="text" class="form-control fund_percent_input" data-feature-id="" data-fund-id="<?= $fund->id; ?>" value="<?= $fund->percent; ?>">
                    <span class="form-control-feedback">%</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <hr>
        <h4>Цена "Для всех"</h4>
        <hr>
        <input type="checkbox" id="fund_common_price_check">&nbsp;&nbsp;Использовать <?php echo($constants["PERCENT_FOR_ALL"])?>% наценку
        <br />
        <br />
        <div class="has-feedback">
            <input type="text" class="form-control" id="fund_common_price_input" data-feature-id="" value="" readonly>
            <span class="form-control-feedback">руб.</span>
        </div>

    <?php Modal::end(); ?>
<?php endif; ?>