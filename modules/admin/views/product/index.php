<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;
use app\models\ProductPrice;
use app\models\ProductFeature;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Товары';
$this->params['breadcrumbs'][] = $this->title;

$updateVisibilityUrl = Url::to(['/api/profile/admin/product/update-visibility']);
$updatePublishedUrl = Url::to(['/api/profile/admin/product/update-published']);
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
                    alert('Ошибка обновления видимости товара');
                }
            },
            error: function () {
                alert('Ошибка обновления видимости товара');
            },
        });

        return false;
    });

    $('input[type="checkbox"][class="update-published"]').on('change', function () {
        $.ajax({
            url: '$updatePublishedUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-product-id'),
                published: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    alert('Ошибка обновления опубликования товара');
                }
            },
            error: function () {
                alert('Ошибка обновления опубликования товара');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="product-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить товар', ['create'], ['class' => 'btn btn-success']) ?>
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
                // 'label' => 'Название',
                'attribute' => 'name',
                'content' => function ($model) {
                    return $model->name . ProductFeature::getFeatureByProduct($model->id) . '<strong>' . ProductFeature::getPurchaseTypeProduct($model->id) . '</strong>';
                }
            ],
            [
                'label' => 'Цена для участников',
                'content' => function ($model) {
                    return ProductPrice::getMemberPriceByProduct($model->id);
                }
            ],
            [
                'label' => 'Цена для всех',
                'content' => function ($model) {
                    return ProductPrice::getAllPriceByProduct($model->id);
                }
            ],
            [
                'label' => 'Количество',
                'content' => function ($model) {
                    return ProductFeature::getQuantityByProduct($model->id);
                }
            ],
            [
                'attribute' => 'visibility',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->visibility ? 'checked' : '') . ' data-product-id="' . $model->id . '" class="update-visibility">';
                }
            ],
            [
                'attribute' => 'published',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->published ? 'checked' : '') . ' data-product-id="' . $model->id . '" class="update-published">';
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
        
        'layout'=>"{pager}\n{summary}\n{items}\n{pager}",
        
    ]); ?>

</div>
