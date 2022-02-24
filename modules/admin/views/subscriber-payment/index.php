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

    <style>
    .SP_table {
        /* border: 1px solid lightgrey; */
        margin-bottom: 20px;
    }
    .SP_table td {
        border: 1px solid lightgrey;
        padding: 5px 10px;
    }
    .SP_table thead {
        font-weight: 700;
    }
    .SP_table button {
        border: 1px solid lightgrey;
        padding: 5px 10px;
        margin: 10px;
    }
    </style>

    <table class="SP_table">
        <thead>
            <tr>
                <td>&nbsp;№&nbsp;<br />п/п</td>
                <td>Ф.И.О</td>
                <td>Название группы</td>
                <td>Статус контрагента</td>
                <td>Сумма к оплате</td>
                <td>Распечатать<br />Приходно кассовый ордер</td>
                <td>Исключить из текущего списка на месяц</td>
                <td>Отметка к исключению</td>
            </tr>
        </thead>
        <tbody>
        <?php
            $echo = "";
            $key = 1;
            // foreach (array_reverse($subscriber_messages) as $value) {
            foreach ($subscriber_messages as $value) {
                $role = "Участник";
                if ($value->role === "partner") $role = "Партнёр";
                if ($value->role === "provider") $role = "Поставщик";
                $echo .= "<tr>";
                $echo .= "<td>" . $key++ . "</td>";
                $echo .= "<td>" . $value->fullName . "</td>";
                $echo .= "<td>" . $value->partner->name . "</td>";
                $echo .= "<td>" . $role . "</td>";
                // $echo .= "<td>" . $value->subscriber->number_of_times . "</td>";
                $echo .= "<td>" . $value->amount . "</td>";
                $echo .= "<td><button>Действия</button></td>";
                if ($value->subscriber->number_of_times) $echo .= "<td>Долг</td>";
                else $echo .= "<td>Нет долга</td>";
                if ($value->subscriber->number_of_times >= 3) $echo .= "<td>Исключить<br />контрагента</td>";
                else $echo .= "<td>&nbsp;</td>";
                $echo .= "</tr>";
            }
            echo $echo;
        ?>
        </tbody>
    </table>

    
    <?php
    // echo GridView::widget([ 
    //     'dataProvider' => $dataProvider,
    //     'columns' => [
    //         ['class' => 'yii\grid\SerialColumn'],

    //         'created_at',
    //         'amount',
    //         'fullName',
    //         'visible',
    //         'number_of_times',

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
