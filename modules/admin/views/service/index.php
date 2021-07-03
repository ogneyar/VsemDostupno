<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Услуги';
$this->params['breadcrumbs'][] = $this->title;

$updateVisibilityUrl = Url::to(['/api/profile/admin/service/update-visibility']);
$updatePublishedUrl = Url::to(['/api/profile/admin/service/update-published']);
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
                    alert('Ошибка обновления видимости услуги');
                }
            },
            error: function () {
                alert('Ошибка обновления видимости услуги');
            },
        });

        return false;
    });

    $('input[type="checkbox"][class="update-published"]').on('change', function () {
        $.ajax({
            url: '$updatePublishedUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-service-id'),
                published: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    alert('Ошибка обновления опубликования услуги');
                }
            },
            error: function () {
                alert('Ошибка обновления опубликования услуги');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="service-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить услугу', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <p>
        <b>Фильтр категорий:</b> <?= Html::dropDownList('category_id', $category_id, $categories, [
            'encode' => false,
            'onchange' => new JsExpression('
                window.location = window.location.href.split("?")[0] + "?category_id=" + $(this).val();
            '),
        ]) ?>
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
                    return '<input type="checkbox" ' . ($model->published ? 'checked' : '') . ' data-service-id="' . $model->id . '" class="update-published">';
                }
            ],
            'price',
            'name',

            ['class' => 'yii\grid\ActionColumn'],
        ],
        
        'layout'=>"{pager}\n{summary}\n{items}\n{pager}",
        
    ]); ?>

</div>
