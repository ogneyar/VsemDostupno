<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$downloadSessionProtocolUrl = Url::to(['download-protocol']);
$script = <<<JS
    $(function () {
        $('#protocol-button').daterangepicker({
            'locale': {
                'format': 'DD.MM.YYYY',
                'applyLabel': 'Загрузить',
                'cancelLabel': 'Отменить',
                'weekLabel': 'W',
                'daysOfWeek': moment.weekdaysMin(),
                'monthNames': moment.monthsShort(),
                'firstDay': moment.localeData()._week.dow
            },
            'language': 'ru',
            'autoUpdateInput': false,
        });

        $('#protocol-button').on('apply.daterangepicker', function(ev, picker) {
            window.location.href = '$downloadSessionProtocolUrl?' +
                'startDate=' + picker.startDate.format('YYYY-MM-DD') + '&' +
                'endDate=' + picker.endDate.format('YYYY-MM-DD');
        });
    })
JS;
$this->registerJs($script, $this::POS_END);

$this->title = 'Участники';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить участника', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Протокол совета', '#', ['class' => 'btn btn-success', 'id' => 'protocol-button']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'number',
            'created_at',
            'disabled',
            'cityName',
            'partnerName',
            'fullName',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {
                        return Html::beginTag('div', ['class'=>'dropdown']) .
                            Html::button('Действия <span class="caret"></span>', [
                                'type'=>'button',
                                'class'=>'btn btn-default',
                                'data-toggle'=>'dropdown'
                            ]) .
                            DropdownX::widget([
                            'items' => [
                                [
                                    'label' => 'Счета',
                                    'url' => Url::to(['user/account', 'id' => $model->user->id]),
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Просмотр',
                                    'url' => Url::to(['view', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Редактировать',
                                    'url' => Url::to(['update', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Удалить',
                                    'url' => Url::to(['delete', 'id' => $model->id]),
                                    'linkOptions' => [
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить этого участника?',
                                            'method' => 'post',
                                        ],
                                    ]
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Заявление',
                                    'url' => Url::to(['user/download-request', 'id' => $model->user->id]),
                                ],
                                [
                                    'label' => 'Анкета',
                                    'url' => Url::to(['user/download-questionary', 'id' => $model->user->id]),
                                ],
                                [
                                    'label' => 'Договор-оферта',
                                    'url' => Url::to(['user/download-offer', 'id' => $model->user->id]),
                                ],
                                [
                                    'label' => 'Договор хоз. деят.',
                                    'url' => Url::to(['user/download-business', 'id' => $model->user->id]),
                                ],
                                [
                                    'label' => 'Паевой взнос',
                                    'url' => Url::to(['user/download-incoming-payment', 'id' => $model->user->id]),
                                ],
                                [
                                    'label' => 'Членский взнос (мес.)',
                                    'url' => Url::to(['user/download-user-payment-by-months', 'id' => $model->user->id]),
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
                                [
                                    'label' => 'Членский взнос (руб.)',
                                    'url' => Url::to(['user/download-user-payment-by-cost', 'id' => $model->user->id]),
                                    'linkOptions' => [
                                        'onclick' => new JsExpression("
                                            var cost = prompt('Введите сумму членского взноса:');
                                            if (cost) {
                                                if (!cost.match(/^\d*\.?\d*$/)) {
                                                    alert('Ошибка при вводе суммы!');
                                                    return false;
                                                }
                                                window.location.href = $(this).attr('href') + '&cost=' + cost;
                                            }

                                            return false;
                                        "),
                                    ]
                                ],
                                [
                                    'label' => 'Членский взнос (кв-л)',
                                    'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->user->id]),
                                ],
                            ],
                        ]) .
                        Html::endTag('div');
                    }
                ],
            ],
        ],
    ]); ?>

</div>

<?= DateRangePicker::widget([
    'name'=>'date_range_dummy',
    'hideInput' => true,
    'containerOptions' => [
        'style' => 'display: none;',
    ],
]) ?>
