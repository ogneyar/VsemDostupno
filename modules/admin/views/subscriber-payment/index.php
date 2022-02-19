<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;
use app\models\User;
use app\models\Parameter;
use app\models\Account;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Членские взносы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subscriber-payment-index">
    
    
    <h1><?= Html::encode($this->title) ?></h1>

    <hr/>
    <label>
        <input <?php if (!$superadmin) echo("disabled"); ?> id="input_changed_subscriber_payment_total" class="btn btn-default" type="number" placeholder="Введите сумму" value="<?=$account?>" style="width:150px;"/> 
        <?php if ($superadmin) echo '<button id="button_changed_subscriber_payment_total" class="btn btn-default">Сохранить</button>'; ?>
        <label>Сумма "Членских взносов" взымаемая ежемесячно (0 - для отключения)</label>
    </label>
    <hr/>

    <script>
        document.getElementById("button_changed_subscriber_payment_total")
            ?.addEventListener("click", async function() {
                let value = document.getElementById("input_changed_subscriber_payment_total").value
                let url = '<?=$web?>/site/run/update-subscription?value=' + value;
                let response = await fetch(url, {method:"get"});

                let com = await response.json()
                // console.log(com);
                alert(com.message);
            })
    </script>

    <?php
    // echo GridView::widget([ 
    //     'dataProvider' => $dataProvider,
    //     'columns' => [
    //         ['class' => 'yii\grid\SerialColumn'],

    //         'created_at',
    //         'amount',
    //         'fullName',

    //         [
    //             'class' => 'yii\grid\ActionColumn',
    //             'template' => '{actions}',
    //             'buttons' => [
    //                 'actions' => function ($url, $model) {
    //                     if ($model->amount < User::SUBSCRIBER_MONTHS_INTERVAL * (int) Parameter::getValueByName('subscriber-payment')) {
    //                         $items = [
    //                             [
    //                                 'label' => 'Членский взнос (мес.)',
    //                                 'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->user->id, 'months' => (int) ($model->amount / (int) Parameter::getValueByName('subscriber-payment'))]),
    //                             ],
    //                         ];
    //                     } else {
    //                         $items = [
    //                             [
    //                                 'label' => 'Членский взнос (кв-л)',
    //                                 'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->user->id]),
    //                             ],
    //                         ];
    //                     }
    //                     return Html::beginTag('div', ['class'=>'dropdown']) .
    //                         Html::button('Действия <span class="caret"></span>', [
    //                             'type'=>'button',
    //                             'class'=>'btn btn-default',
    //                             'data-toggle'=>'dropdown'
    //                         ]) .
    //                         DropdownX::widget([
    //                         'items' => $items,
    //                     ]) .
    //                     Html::endTag('div');
    //                 }
    //             ],
    //         ],
    //     ],
    // ]); 
    ?>

</div>
