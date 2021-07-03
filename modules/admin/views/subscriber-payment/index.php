<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;
use app\models\User;
use app\models\Parameter;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Членские взносы';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subscriber-payment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'created_at',
            'amount',
            'fullName',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'buttons' => [
                    'actions' => function ($url, $model) {
                        if ($model->amount < User::SUBSCRIBER_MONTHS_INTERVAL * (int) Parameter::getValueByName('subscriber-payment')) {
                            $items = [
                                [
                                    'label' => 'Членский взнос (мес.)',
                                    'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->user->id, 'months' => (int) ($model->amount / (int) Parameter::getValueByName('subscriber-payment'))]),
                                ],
                            ];
                        } else {
                            $items = [
                                [
                                    'label' => 'Членский взнос (кв-л)',
                                    'url' => Url::to(['user/download-user-payment-by-quarter', 'id' => $model->user->id]),
                                ],
                            ];
                        }
                        return Html::beginTag('div', ['class'=>'dropdown']) .
                            Html::button('Действия <span class="caret"></span>', [
                                'type'=>'button',
                                'class'=>'btn btn-default',
                                'data-toggle'=>'dropdown'
                            ]) .
                            DropdownX::widget([
                            'items' => $items,
                        ]) .
                        Html::endTag('div');
                    }
                ],
            ],
        ],
    ]); ?>

</div>
