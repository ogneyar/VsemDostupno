<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use app\models\City;
// use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model app\models\Member */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-form">

    <div class="form-group">
        <label for="memberform-user_id" class="control-label">Рекомендатель <span style="color:red;">*</span></label>
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
                    'editableSuccess' => 'function(event, val, form, data) { $(\'#memberform-recommender_id\').val(data[\'user_id\']); }',
                ],
                'options' => [
                    'class' => 'form-control',
                ],
            ]) ?>
        </div>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'disabled')->checkbox() ?>

    <?= $form->field($model, 'number') ?>

    <?php
        $data = [];
        foreach (City::find()->each() as $city) {
            $data[$city->name] = ArrayHelper::map($city->partners, 'id', 'name');
        }
        echo $form->field($model, 'partner')->widget(Select2::className(), [
            'data' => $data,
            'language' => substr(Yii::$app->language, 0, 2),
            'options' => ['placeholder' => 'Выберите партнера ...'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>

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

    <?= $form->field($model, 'itn') ?>

    <?= $form->field($model, 'recommender_info')->hiddenInput(['value'=> ''])->label(false) ?>

    <?= $form->field($model, 'recommender_id', ['template' => "{label}\n<div>{input}</div>\n<div>{error}</div>"])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'skills')->textArea(['rows' => '6']) ?>
    <?= $form->field($model, 'become_provider')->checkbox();?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
