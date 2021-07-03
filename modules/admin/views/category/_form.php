<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use mihaildev\ckeditor\CKEditor;
use kartik\file\FileInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $form yii\widgets\ActiveForm */

$this->registerJs("CKEDITOR.plugins.addExternal('youtube', '/ckeditor/plugins/youtube/youtube/plugin.js', '');");
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>

    <?= $form->field($model, 'visibility')->checkbox() ?>
    
    <?= $form->field($model, 'for_reg')->checkbox() ?>

    <?= $form->field($model, 'order') ?>

    <?= $form->field($model, 'slug') ?>
    
    <?= $form->field($model, 'external_link') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'purchaseDate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
        'readonly' => true,
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            'startDate' => (new \DateTime('now'))->format('Y-m-d'),
        ],
    ]) ?>

    <?= $form->field($model, 'orderDate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
        'readonly' => true,
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd',
            'startDate' => (new \DateTime('now'))->format('Y-m-d'),
        ],
    ]) ?>

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
        if ($model->photo) {
            $initialPreview[] = Html::img($model->thumbUrl);
            $initialPreviewConfig[] = [
                'url' => Url::to(['/api/profile/admin/photo/delete']),
                'extra' => [
                    'PhotoDeletion[key]' => $model->photo->id,
                    'PhotoDeletion[id]' => $model->id,
                    'PhotoDeletion[class]' => $model->className(),
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
