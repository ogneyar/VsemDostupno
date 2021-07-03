<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
$this->title = 'Личные данные';
$this->params['breadcrumbs'] = [$this->title];

$script = <<<JS
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            placement: 'top',
            container: 'body'
        });
    });
JS;
$this->registerJs($script, $this::POS_END);
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<?php $form = ActiveForm::begin([
    'id' => 'personal-form',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
        'labelOptions' => ['class' => 'col-md-2 control-label'],
    ],
]); ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Предоставление неверных данных влечёт за собой автоматическое исключение из числа участников.</p>
        </div>
    </div>

    <?= $form->field($model, 'lastname') ?>

    <?= $form->field($model, 'firstname') ?>

    <?= $form->field($model, 'patronymic') ?>

    <?= $form->field($model, 'birthdate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd.mm.yyyy',
        ],
    ]) ?>

    <?= $form->field($model, 'citizen') ?>

    <?= $form->field($model, 'registration', [
        'inputOptions' => [
            'data-toggle' => 'tooltip',
            'title' => 'Пример адреса: 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1098',
        ],
    ]) ?>

    <?= $form->field($model, 'residence', [
        'inputOptions' => [
            'data-toggle' => 'tooltip',
            'title' => 'Адрес фактического пребывания заполняется в случае отличия от адреса регистрации. Этот адрес будет использоваться для отправки корреспонденции, например, 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1101',
        ],
    ]) ?>

    <?= $form->field($model, 'passport') ?>

    <?= $form->field($model, 'passport_date')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd.mm.yyyy',
        ],
    ]) ?>

    <?= $form->field($model, 'passport_department') ?>

    <?= $form->field($model, 'itn') ?>

    <?= $form->field($model, 'skills')->textArea(['rows' => '6']) ?>

    <?= $form->field($model, 'phone')->widget(
        MaskedInput::className(), [
        'mask' => '+7 (999)-999-9999',
    ]) ?>

    <?= $form->field($model, 'ext_phones') ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Введите пароль для доступа к ресурсу.</p>
        </div>
    </div>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'password_repeat')->passwordInput() ?>

    <div class="form-group">
        <div class="col-md-8">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary pull-right', 'name' => 'save-button']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
