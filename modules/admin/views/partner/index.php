<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Партнеры';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="partner-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить партнера', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'number',
            'created_at',
            'disabled',
            'cityName',
            'name',
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
                                            'confirm' => 'Вы уверены, что хотите удалить этого партнера?',
                                            'method' => 'post',
                                        ],
                                        'class' => $model->members ? 'hidden' : '',
                                    ]
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Адрес для поставщика',
                                    'url' => Url::to(['address', 'id' => $model->id]),
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
