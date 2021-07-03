<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use mihaildev\ckeditor\CKEditor;
use app\models\Category;
use app\models\Product;
use wbraganca\fancytree\FancytreeWidget;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("CKEDITOR.plugins.addExternal('youtube', '/ckeditor/plugins/youtube/youtube/plugin.js', '');");
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>

    <?= $form->field($model, 'visibility')->checkbox() ?>

    <?= $form->field($model, 'categoryIds')->hiddenInput()->label(false) ?>

    <div class="form-group field-product-categories">
        <label for="product-categories" class="control-label">Категории</label>
        <?php
            $selected = array_keys(ArrayHelper::map($model->categories, 'id', 'name'));

            echo FancytreeWidget::widget([
            'id' => 'w99',
            'options' =>[
                'source' => Category::getFancyTree($selected, $model->providerCategories, false),
                'checkbox' => true,
                'extensions' => ['edit', 'glyph', 'wide'],
                'selectMode' => 2,
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
                    $("input[name=\"Product[categoryIds]\"]").val(JSON.stringify(keys));
                }'),
                'init' => new JsExpression('function (event, data) {
                    var init = $(this).fancytree("option", "select");
                    init(event, data);
                }'),
            ]
        ]) ?>
    </div>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'purchase_price') ?>

    <?= $form->field($model, 'price') ?>

    <?= $form->field($model, 'inventory') ?>

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
                'url' => Url::to(['/api/profile/provider/product/delete-photo']),
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

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
