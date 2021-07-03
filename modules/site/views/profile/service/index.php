<?php

use yii\helpers\Url;
use yii\grid\GridView;
use kartik\helpers\Html;
use kartik\dropdown\DropdownX;

/* @var $this yii\web\View */
$this->title = 'Мои услуги';
$this->params['breadcrumbs'] = [$this->title];
$updateVisibilityUrl = Url::to(['/api/profile/service/update-visibility']);
$script = <<<JS
$(function () {
    $('input[type="checkbox"][class="update-visibility"]').on('change', function () {
        $.ajax({
            url: '$updateVisibilityUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-service-id'),
                visibility: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    yii.alert('Ошибка обновления видимости услуги');
                }
            },
            error: function () {
                yii.alert('Ошибка обновления видимости услуги');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="service-index">
    <p>
        <?= Html::a('Добавить услугу', Url::to(['profile/service/create']), ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'visibility',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->visibility ? 'checked' : '') . ' data-service-id="' . $model->id . '" class="update-visibility">';
                }
            ],
            [
                'attribute' => 'published',
                'content' => function ($model) {
                    return $model->published ? 'Да' : 'Нет';
                }
            ],
            'price',

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
                                            'confirm' => 'Вы уверены, что хотите удалить этот услугу?',
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
