<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
// use yii\widgets\MaskedInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Candidate */
/* @var $form yii\widgets\ActiveForm */

$dd_items = ArrayHelper::map($groups, 'id', 'name'); 
?>

<div class="candidate-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'group_id')->dropDownList($dd_items) ?>
    
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'birthdate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]) ?>

    <?/*= $form->field($model, 'phone')->widget(
        MaskedInput::className(), [
        'mask' => '+7 (999)-999-9999',
    ]) */?>
    
    <?= $form->field($model, 'phone') ?>
    
    <?= $form->field($model, 'comment')->textArea(); ?>

    <?= $form->field($model, 'block_mailing')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
