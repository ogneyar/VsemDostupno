<?php

use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\editable\Editable;
use app\models\Account;
use app\helpers\Html;

/* @var $this yii\web\View */
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Счета', 'url' => ['/profile/account']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="row">
    <label for="accounttransfer-user_id" class="col-lg-2 control-label text-right">Пользователь</label>
    <div class="col-lg-4 user-account-transfer">
        <?= Editable::widget([
            'name'=>'UserSearching[search]',
            'displayValue' => $toUserFullName,
            'asPopover' => true,
            'size' => 'md',
            'header' => 'Пользователя. Вы можете найти пользователя-получателя по емайл или по номеру регистрации.',
            'resetButton' => ['style' => 'display: none;'],
            'formOptions' => [
                'action' => Url::to(['/api/profile/default/search-user']),
            ],
            'pluginEvents' => [
                'editableSuccess' => 'function(event, val, form, data) { $(\'input[name="TransferForm[to_user_id]"]\').val(data[\'user_id\']); }',
            ],
            'options' => [
                'class' => 'form-control',
            ],
        ]) ?>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'swap-form',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-2 control-label'],
    ],
]); ?>

<?= $form->field($model, 'to_user_id', ['template' => "{label}\n<div class=\"col-lg-2\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>"])->hiddenInput()->label(false) ?>

<?php
    $data = [];
    foreach ($user->accounts as $account) {
        $data[$account->type] = sprintf(
            '%s (%s)',
            Html::makeTitle($account->typeName),
            $account->total
        );
    }
?>

<?= $form->field($model, 'amount')->textInput() ?>

<?= $form->field($model, 'message')->textArea(['rows' => '2']) ?>

<div class="form-group">
    <div class="col-lg-6">
        <?= Html::submitButton('Перевести', ['class' => 'btn btn-primary pull-right', 'name' => 'swap-button']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
