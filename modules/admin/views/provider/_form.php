<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
// use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use wbraganca\fancytree\FancytreeWidget;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use kartik\select2\Select2;
use app\models\Partner;
use app\models\Category;
use app\models\City;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="provider-form">

    <div class="form-group">
        <label for="providerform-user_id" class="control-label">Рекомендатель <span style="color:red;">*</span></label>
        <div>
            <?= Editable::widget([
                'name'=>'UserSearching[search]',
                'displayValue' => $model->recommender ? $model->recommender->fullName : '(не задан)',
                'asPopover' => true,
                'size' => 'md',
                'header' => 'Рекомендателя. Вы можете найти пользователя по емайл или по номеру регистрации.',
                'resetButton' => ['style' => 'display: none;'],
                'formOptions' => [
                    'action' => Url::to(['/api/profile/default/search-user']),
                ],
                'pluginEvents' => [
                    'editableSuccess' => 'function(event, val, form, data) { $(\'#providerform-recommender_id\').val(data[\'user_id\']); }',
                ],
                'options' => [
                    'class' => 'form-control',
                ],
            ]) ?>
        </div>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?php
        $data = [];
        foreach (City::find()->each() as $city) {
            $partners = Partner::find()
                ->joinWith(['user'])
                ->where('{{%partner}}.city_id = :city_id AND {{%user}}.disabled = 0', [':city_id' => $city->id])
                ->all();
            if ($partners) {
                $data[$city->name] = ArrayHelper::map($partners, 'id', 'name');
            }
        }
        echo $form->field($model, 'partner')->widget(Select2::className(), [
            'data' => $data,
            'language' => substr(Yii::$app->language, 0, 2),
            'options' => [
                'placeholder' => 'Выберите партнера ...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>

    <?= $form->field($model, 'disabled')->checkbox() ?>

    <?= $form->field($model, 'categoryIds')->hiddenInput()->label(false) ?>

    <div class="form-group field-provider-categories required">
        <label for="provider-categories" class="control-label">Категории</label>
        <?php
            $selected = array_keys(ArrayHelper::map($model->categories, 'id', 'name'));

            echo FancytreeWidget::widget([
            'id' => 'w99',
            'options' =>[
                'source' => Category::getFancyTree($selected),
                'checkbox' => true,
                'extensions' => ['edit', 'glyph', 'wide'],
                'selectMode' => 3,
                'glyph' => [
                    'map' => [
                        'doc' => 'glyphicon glyphicon-book',
                        'docOpen' => 'glyphicon glyphicon-book',
                        'checkbox' => 'glyphicon glyphicon-unchecked',
                        'checkboxSelected' => 'glyphicon glyphicon-check',
                        'checkboxUnknown' => 'glyphicon glyphicon-share',
                        'dragHelper' => 'glyphicon glyphicon-play',
                        'dropMarker' => 'glyphicon glyphicon-arrow-right',
                        'error' => 'glyphicon glyphicon-warning-sign',
                        'expanderClosed' => 'glyphicon glyphicon-plus-sign',
                        'expanderLazy' => 'glyphicon glyphicon-plus-sign',
                        'expanderOpen' => 'glyphicon glyphicon-minus-sign',
                        'folder' => 'glyphicon glyphicon-list',
                        'folderOpen' => 'glyphicon glyphicon-list',
                        'loading' => 'glyphicon glyphicon-refresh',
                    ],
                ],
                'select' => new JsExpression('function (event, data) {
                    var keys = [];
                    $.map(data.tree.getSelectedNodes(), function (node) {
                        keys.push(node.key);
                    });
                    $("input[name=\"ProviderForm[categoryIds]\"]").val(JSON.stringify(keys));
                }'),
                'init' => new JsExpression('function (event, data) {
                    var init = $(this).fancytree("option", "select");
                    init(event, data);
                }'),
            ]
        ]) ?>
    </div>

    <?//= $form->field($model, 'number') ?>

    <?= $form->field($model, 'tg_id') ?>

    <?= $form->field($model, 'name') ?>
    
    <?= $form->field($model, 'field_of_activity') ?>

    <?= $form->field($model, 'legal_address') ?>
    
    <?= $form->field($model, 'itn') ?>
    
    <?= $form->field($model, 'snils') ?>
    
    <?= $form->field($model, 'ogrn') ?>
    
    <?= $form->field($model, 'site') ?>
    
    <?= $form->field($model, 'description')->textarea() ?>

    <?php if ($model->isNewRecord): ?>
        <?= $form->field($model, 'email') ?>
    <?php endif ?>

    <?= $form->field($model, 'phone') ?>

    <!-- <?/*= $form->field($model, 'phone')->widget(
        MaskedInput::className(), [
        'mask' => '+7 (999)-999-9999',
    ]) */?> -->

    <?= $form->field($model, 'ext_phones') ?>

    <?= $form->field($model, 'lastname') ?>

    <?= $form->field($model, 'firstname') ?>

    <?= $form->field($model, 'patronymic') ?>

    <?= $form->field($model, 'birthdate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]) ?>

    <?= $form->field($model, 'citizen') ?>

    <?= $form->field($model, 'registration') ?>

    <?= $form->field($model, 'residence') ?>

    <?= $form->field($model, 'passport') ?>

    <?= $form->field($model, 'passport_date')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]) ?>

    <?= $form->field($model, 'passport_department') ?>

    <?= $form->field($model, 'recommender_id', ['template' => "{label}\n<div>{input}</div>\n<div>{error}</div>"])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'skills')->textArea(['rows' => '6']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
