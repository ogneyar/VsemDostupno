<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
// use yii\widgets\MaskedInput;

$dd_items = [];
if (count($groups)) {
    $dd_items = ArrayHelper::map($groups, 'id', 'name');
    $dd_param = ['options' => [$last_candidate->group_id => ['selected' => true]]];
}
?>
<?php $form = ActiveForm::begin(['id' => 'add-candidate-frm', 'enableAjaxValidation' => true]); ?>
    
    <?= $form->field($modelCandidate, 'group_id')->dropDownList($dd_items, $dd_param) ?>
    
    <?= $form->field($modelCandidate, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($modelCandidate, 'fio')->textInput(['maxlength' => true]) ?>

    <?= $form->field($modelCandidate, 'birthdate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]) ?>

    <?= $form->field($modelCandidate, 'phone') ?>
    
    <?/*= $form->field($modelCandidate, 'phone')->widget(
        MaskedInput::className(), [
        'mask' => '+7 (999)-999-9999',
    ]) */?>
    
    <?= $form->field($modelCandidate, 'comment')->textArea(); ?>

    <?= $form->field($modelCandidate, 'block_mailing')->checkbox() ?>
    
    <div class="form-group" style="text-align: right;">
        <?= Html::button('Закрыть', ['class' => 'btn btn-default', 'data-dismiss' => 'modal', 'aria-hidden' => 'true']) ?>
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
    </div>

<?php ActiveForm::end(); ?>

