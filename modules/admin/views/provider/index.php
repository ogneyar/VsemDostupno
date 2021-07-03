<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Поставщики';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provider-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить поставщика', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Принять на склад', ['/admin/stock/create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Учёт товаров/остатки',['/admin/stock/index'], ['class'=>'btn btn-success']) ?>
        <?php if (Yii::$app->hasModule('purchase')): ?>
            <?= Html::a('Оформить закупку',['/admin/purchase/create'], ['class'=>'btn btn-success']) ?>
        <?php endif; ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'number',
            'created_at',
            'disabled',
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
                                    'url' => Url::to(['account', 'id' => $model->id]),
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
                                            'confirm' => 'Вы уверены, что хотите удалить этого поставщика?',
                                            'method' => 'post',
                                        ],
                                    ]
                                ],
                                '<li class="divider"></li>',
                                [
                                    'label'=> 'Товары поставщика',
                                    'url' => Url::to(['/admin/product/provider', 'id'=> $model->id]),
                                ],
                                [
                                    'label'=> 'История поставок',
                                    'url' => Url::to(['/admin/stock/view', 'id'=> $model->id]),
                                ],
                                [
                                        'label'=>'Перевод пая на лицевой счёт',
                                        'url'=>Url::to(['/admin/stock/unit', 'id'=>$model->id]),
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
