<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use mihaildev\ckeditor\CKEditor;
use app\models\Category;
use app\models\Service;
use wbraganca\fancytree\FancytreeWidget;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model app\models\Service */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("CKEDITOR.plugins.addExternal('youtube', '/ckeditor/plugins/youtube/youtube/plugin.js', '');");
?>

<div class="service-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>

    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'visibility')->checkbox() ?>

    <?= $form->field($model, 'categoryIds')->hiddenInput()->label(false) ?>

    <div class="form-group field-service-categories">
        <label for="service-categories" class="control-label">Категории</label>
        <?php
            $selected = array_keys(ArrayHelper::map($model->categories, 'id', 'name'));
            $category = Category::findOne(['slug' => Category::SERVICE_SLUG]);
            $tree = $category ? $category->children()->all() : [];

            echo FancytreeWidget::widget([
            'id' => 'w99',
            'options' =>[
                'source' => Category::getFancyTree($selected, $tree, false),
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
                    $("input[name=\"Service[categoryIds]\"]").val(JSON.stringify(keys));
                }'),
                'init' => new JsExpression('function (event, data) {
                    var init = $(this).fancytree("option", "select");
                    init(event, data);
                }'),
            ]
        ]) ?>
    </div>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'price') ?>

    <?= $form->field($model, 'member_price') ?>

    <?= $form->field($model, 'contacts')->textarea(['rows' => 5]) ?>

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
        foreach ($model->serviceHasPhoto as $item) {
            $initialPreview[] = Html::img($item->thumbUrl);
            $initialPreviewConfig[] = [
                'url' => Url::to(['/api/profile/service/delete-photo']),
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
                'maxFileCount' => Service::MAX_FILE_COUNT,
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
