<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
$this->title = 'Участники группы';
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'fullName',
        'logged_in_at',
        'email:email',
        [
            'class' => 'yii\grid\DataColumn',
            'label' => 'Телефон',
            'content' => function ($model) { return Html::a(Html::encode($model->phone), 'tel:' . $model->phone); },
        ],
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
                                'url' => Url::to(['account', 'id' => $model->id]),
                            ],
                            [
                                'label' => 'Редактировать',
                                'url' => Url::to(['update', 'id' => $model->id]),
                            ],
                        ],
                    ]) .
                    Html::endTag('div');
                }
            ],
        ],
    ],
]) ?>
