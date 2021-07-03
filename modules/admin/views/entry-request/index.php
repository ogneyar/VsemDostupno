<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;
use yii\web\JsExpression;

$this->title = 'Заявки на вступление';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'created_at',
            'fullName',
            [
                'attribute' => 'role',
                'content' => function ($model) {
                    if ($model->role == 'member') return 'Участник';
                    if ($model->role == 'partner') return 'Партнёр';
                    if ($model->role == 'admin') return 'Администратор';
                    return 'Поставщик';
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {
                        return Html::beginTag('div', ['class'=>'dropdown']) .
                            Html:: button('Действия <span class="caret"></span>', [
                                'type'=>'button',
                                'class'=>'btn btn-default',
                                'data-toggle'=>'dropdown'
                            ]) .
                            DropdownX::widget([
                            'items' => [
                                [
                                    'label' => 'Просмотр',
                                    'url' => Url::to(['view', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Редактировать',
                                    'url' => Url::to(['update', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Принять',
                                    'url' => Url::to(['accept', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Удалить',
                                    'url' => Url::to(['delete', 'id' => $model->id]),
                                    'linkOptions' => [
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить эту заявку?',
                                            'method' => 'post',
                                        ],
                                    ]
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Заявление',
                                    'url' => Url::to(['user/download-request', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Анкета',
                                    'url' => Url::to(['user/download-questionary', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Договор-оферта',
                                    'url' => Url::to(['user/download-offer', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Договор хоз. деят.',
                                    'url' => Url::to(['user/download-business', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Паевой взнос',
                                    'url' => Url::to(['user/download-incoming-payment', 'id' => $model->id]),
                                ],
                                [
                                    'label' => 'Членский взнос (мес.)',
                                    'url' => Url::to(['user/download-user-payment-by-months', 'id' => $model->id]),
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
                                    'url' => Url::to(['user/download-user-payment-by-cost', 'id' => $model->id]),
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
                                    'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->id]),
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