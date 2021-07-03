<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
$this->title= "Поиск";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
       'lastname:text:Фамилия',
            'firstname:text:Имя',
            'patronymic:text:Отчество',
            [
            'attribute'=>'role',
            'label'=>'Роль',
            'content'=>function($data){
                if($data['role']=="partner")
                {return "Партнёр";}
            elseif($data['role']=='member')
                {return "Участник";}
            else return "Поставщик";
            }
            ],
            'email:text:Email',
            'phone:text:Телефон',
            'name:text:Название организации',
            'number:text:Номер',
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=> '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {

                        if($model['role']=='member'){
                        
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
                                        'url' => Url::to(['user/account', 'id' => $model['user_id']]),
                                    ],
                                    '<li class="divider"></li>',
                                    [
                                        'label' => 'Просмотр',
                                        'url' => Url::to(['member/view', 'id' => $model['member_id']]),
                                    ],
                                    [
                                        'label' => 'Редактировать',
                                        'url' => Url::to(['member/update', 'id' => $model['member_id']]),
                                    ],
                                    [
                                        'label' => 'Удалить',
                                        'url' => Url::to(['member/delete', 'id' => $model['member_id']]),
                                        'linkOptions' => [
                                            'data' => [
                                                'confirm' => 'Вы уверены, что хотите удалить этого партнера?',
                                                'method' => 'post',
                                            ],
                                            'class' => $model['role'] ? 'hidden' : '',
                                        ]
                                    ],
                                    '<li class="divider"></li>',
                                    [
                                        'label' => 'Заявление',
                                        'url' => Url::to(['user/download-request', 'id' => $model['user_id']]),
                                    ],
                                    [
                                        'label' => 'Анкета',
                                        'url' => Url::to(['user/download-questionary', 'id' => $model['user_id']]),
                                    ],
                                    [
                                        'label' => 'Договор-оферта',
                                        'url' => Url::to(['user/download-offer', 'id' => $model['user_id']]),
                                    ],
                                    [
                                        'label' => 'Договор хоз. деят.',
                                        'url' => Url::to(['user/download-business', 'id' => $model['user_id']]),
                                    ],
                                    [
                                        'label' => 'Паевой взнос',
                                        'url' => Url::to(['user/download-incoming-payment', 'id' => $model['user_id']]),
                                    ],
                                    [
                                        'label' => 'Членский взнос (мес.)',
                                        'url' => Url::to(['user/download-user-payment-by-months', 'id' => $model['user_id']]),
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
                                        'url' => Url::to(['user/download-user-payment-by-cost', 'id' => $model['user_id']]),
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
                                        'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model['user_id']]),
                                    ],
                                ],
                            ]) .
                            Html::endTag('div');
                        } else {
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
                                    'url' => Url::to(['user/account', 'id' => $model['user_id']]),
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Просмотр',
                                    'url' => Url::to(['partner/view', 'id' => $model['partner_id']]),
                                ],
                                [
                                    'label' => 'Редактировать',
                                    'url' => Url::to(['partner/update', 'id' => $model['partner_id']]),
                                ],
                                [
                                    'label' => 'Удалить',
                                    'url' => Url::to(['partner/delete', 'id' => $model['partner_id']]),
                                    'linkOptions' => [
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить этого партнера?',
                                            'method' => 'post',
                                        ],
                                        'class' => $model['role'] ? 'hidden' : '',
                                    ]
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label' => 'Заявление',
                                    'url' => Url::to(['user/download-request', 'id' => $model['user_id']]),
                                ],
                                [
                                    'label' => 'Анкета',
                                    'url' => Url::to(['user/download-questionary', 'id' => $model['user_id']]),
                                ],
                                [
                                    'label' => 'Договор-оферта',
                                    'url' => Url::to(['user/download-offer', 'id' => $model['user_id']]),
                                ],
                                [
                                    'label' => 'Договор хоз. деят.',
                                    'url' => Url::to(['user/download-business', 'id' =>$model['user_id']]),
                                ],
                                [
                                    'label' => 'Паевой взнос',
                                    'url' => Url::to(['user/download-incoming-payment', 'id' => $model['user_id']]),
                                ],
                                [
                                    'label' => 'Членский взнос (мес.)',
                                    'url' => Url::to(['user/download-user-payment-by-months', 'id' => $model['user_id']]),
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
                                    'url' => Url::to(['user/download-user-payment-by-cost', 'id' => $model['user_id']]),
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
                                    'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model['user_id']]),
                                ],
                            ],
                        ]) .
                        Html::endTag('div');
                        }
                    }
                ],
             ],
        ],
    ]); ?>

</div>
