<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

$this->title = $title;
$this->params['breadcrumbs'] = [$this->title];
?>

<div class="subscriber-payment-index">
    
    
    <h1><?= Html::encode($this->title) ?></h1>

    <hr/>
    <label>
        <input disabled id="input_changed_subscriber_payment_total" class="btn btn-default" type="number" value="<?=$account?>" style="width:100px;"/> 
        <label>Сумма "Членских взносов" взымаемая ежемесячно</label>
    </label>
    <hr/>
    
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
            width: 120px;
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
            foreach (array_reverse($subscriber_messages) as $value) {
            // foreach ($subscriber_messages as $value) {
                $grey = false;
                $red = false;
                if ($value->subscriber->number_of_times >= 3) $grey = true;
                if ($value->subscriber->number_of_times > 0) $red = true;

                $role = "Участник";
                if ($value->role === "partner") $role = "Партнёр";
                if ($value->role === "provider") $role = "Поставщик";

                if ($grey) {
                    $echo .= "<tr style='background: grey; color: red;'>";
                }else if ($red) {
                    $echo .= "<tr style='color: red;'>";
                }else $echo .= "<tr>";

                $echo .= "<td>" . $key++ . "</td>";
                $echo .= "<td>" . $value->fullName . "</td>";
                $echo .= "<td>" . $value->partner->name . "</td>";
                $echo .= "<td>" . $role . "</td>";
                $echo .= "<td>" . $value->amount . "</td>";
                $echo .= Html::beginTag('td', ['class'=>'dropdown']) .
                    Html::button('Действия <span class="caret"></span>', [
                        'type'=>'button',
                        'class'=>'btn btn-default',
                        'data-toggle'=>'dropdown',
                        // 'style'=>'background: lightgreen;'
                    ]) .
                    DropdownX::widget([ 
                        'items' => [
                            [
                                'label' => 'Членский взнос (мес.)',
                                'url' => Url::to(['user/download-user-payment-by-months', 'id' => $value->user->id]),
                                'linkOptions' => [
                                    'onclick' => new JsExpression("
                                        var months = prompt('Введите количество месяцев оплаты членского взноса:');
                                        if (months) {
                                            if (!months.match(/^\d+$/)) {
                                                alert('Ошибка при вводе количества месяцев!');
                                                return false; 
                                            }
                                            window.location.href = $(this).attr('href') + '&months=' + months;
                                        }
                                        return false; 
                                    "),
                                ]
                            ],
                        ],
                    ]) .
                Html::endTag('td');
                if ($value->subscriber->number_of_times) $echo .= "<td>Долг</td>";
                else $echo .= "<td>Нет долга</td>";

                $echo .= "<td>&nbsp;</td>";

                $echo .= "</tr>";
            }

            // ВЫВОД НА ЭКРАН ВСЕЙ ТАБЛИЦЫ
            echo $echo;

        ?>
        </tbody>
    </table>

</div>