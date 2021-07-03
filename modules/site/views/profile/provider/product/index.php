<?php

use yii\helpers\Url;
use yii\grid\GridView;
use kartik\helpers\Html;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
$this->title = 'Мои товары';
$this->params['breadcrumbs'] = [$this->title];
$updateVisibilityUrl = Url::to(['/api/profile/provider/product/update-visibility']);
$script = <<<JS
$(function () {
    $('input[type="checkbox"][class="update-visibility"]').on('change', function () {
        $.ajax({
            url: '$updateVisibilityUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-product-id'),
                visibility: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    yii.alert('Ошибка обновления видимости товара');
                }
            },
            error: function () {
                yii.alert('Ошибка обновления видимости товара');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="product-index">
    <p>
        <?= Html::a('Добавить товар', Url::to(['/profile/provider/product/create']), ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'visibility',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->visibility ? 'checked' : '') . ' data-product-id="' . $model->id . '" class="update-visibility">';
                }
            ],
            [
                'attribute' => 'published',
                'content' => function ($model) {
                    return $model->published ? 'Да' : 'Нет';
                }
            ],
            'price',
            'member_price',
            'partner_price',
            'inventory',

            [
                'attribute' => 'name',
                'content' => function ($model) {
                    return Html::a(Html::encode($model->name), $model->url);
                }
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
                                    'label' => 'Редактировать',
                                    'url' => Url::to(['update', 'id' => $model->id])
                                ],
                                [
                                    'label' => 'Удалить',
                                    'url' => Url::to(['delete', 'id' => $model->id]),
                                    'linkOptions' => [
                                        'data' => [
                                            'confirm' => 'Вы уверены, что хотите удалить этот товар?',
                                            'method' => 'post',
                                        ],
                                    ]
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
