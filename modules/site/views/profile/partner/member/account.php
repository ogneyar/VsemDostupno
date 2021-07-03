<?php

use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use app\models\Account;
use app\models\Parameter;
use app\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Счета участника';
$this->params['breadcrumbs'][] = ['label' => 'Мои участники', 'url' => Url::to(['/profile/partner/member'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="member-account">

    <h2>Форма пополнения/списания</h2>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <?php
        $data = [];
        foreach ($user->accounts as $account) {
            $data[$account->type] = sprintf(
                '%s (%s)',
                Html::makeTitle($account->typeName),
                $account->total
            );
        }
        echo $form->field($model, 'account_type')->dropDownList($data);
    ?>

    <?= $form->field($model, 'amount') ?>

    <?= $form->field($model, 'message')->textInput([
        'list' => 'messages',
        'autocomplete' => 'off',
    ]) ?>

    <datalist id="messages">
        <?php foreach (explode(';', Parameter::getValueByName('account-messages')) as $message): ?>
            <option value="<?= Html::encode($message) ?>" />
        <?php endforeach ?>
    </datalist>

    <div class="form-group">
        <?= Html::submitButton('Выполнить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <h2>Детализация по счетам</h2>

    <?php
        $items = [];
        foreach ($accounts as $account) {
            $items[] = [
                'label' => $account['name'],
                'content' => GridView::widget([
                    'dataProvider' => $account['dataProvider'],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'created_at',
                        'amount',
                        'fromUserFullName',
                        'toUserFullName',
                        'message',
                    ],
                ]),
                'active' => $accountType == $account['account']->type,
            ];
        }
        echo TabsX::widget(['items' => $items]);
    ?>

</div>
