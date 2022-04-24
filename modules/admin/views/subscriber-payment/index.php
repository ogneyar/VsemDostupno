<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;
use app\models\User;
use app\models\Member;
use app\models\Partner;
use app\models\Provider;
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
        <input <?php if (!$superadmin) echo("disabled"); ?> id="input_changed_subscriber_payment_total" class="btn btn-default" type="number" placeholder="Введите сумму" value="<?=$account?>" style="width:100px;"/> 
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
                else $echo .= "<td>
                    <button 
                        data-delete_name='$value->fullName' 
                        data-delete_id='$value->id' 
                        class='button_delete_subscriber_message'
                        style='background: lightgreen;'
                    >
                        Нет долга
                    </button>
                </td>";

                if ($grey) {
                    $user_id = $value->user->id;
                    if ($value->role == "member") {
                        $contragent = Member::find()->where(['user_id' => $user_id])->one();
                    }else if ($value->role == "partner") {
                        $contragent = Partner::find()->where(['user_id' => $user_id])->one();
                    }else if ($value->role == "provider") {
                        $contragent = Provider::find()->where(['user_id' => $user_id])->one();
                    }
                    $echo .= "<td>
                        <button 
                            data-redirect_id='$contragent->id' 
                            data-redirect_role='$value->role' 
                            class='button_redirect_subscriber_message'
                            style='background: lightgrey; color: black;'
                        >
                            Исключить<br />контрагента
                        </button>
                    </td>";
                }else $echo .= "<td>&nbsp;</td>";

                $echo .= "</tr>";
            }

            // ВЫВОД НА ЭКРАН ВСЕЙ ТАБЛИЦЫ
            echo $echo;

        ?>
        </tbody>
    </table>

    <script>
        let elements = document.getElementsByClassName("button_delete_subscriber_message")
        for (let i = 0; i < elements.length; i++) {
            elements[i]?.addEventListener("click", async function() {
                var yes = confirm(`Вы уверенны, что хотите исключить ${this.dataset.delete_name} из таблицы до нового периода?`);
                if (yes) window.location.href = `<?=$web?>/site/run/delete-record-subscriber-messages?id=${this.dataset.delete_id}&return=<?=$web?>/admin/subscriber-payment`;
            })
        }
    </script>

    <script>
        let redirects = document.getElementsByClassName("button_redirect_subscriber_message")
        for (let i = 0; i < redirects.length; i++) {
            redirects[i]?.addEventListener("click", async function() {
                window.location.href = `<?=$web?>/admin/${this.dataset.redirect_role}/view?id=${this.dataset.redirect_id}`;
            })
        }
    </script>

</div>
