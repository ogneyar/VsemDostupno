<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\models\ProductFeature;
use app\models\ProductPrice;

$this->title = 'Товары поставщика "' . $provider->name . '"';
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['/admin/provider']];
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
        <?= Html::a('Добавить товар', ['create?provider_id=' . $provider->id], ['class' => 'btn btn-success']) ?>
    </p>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'label' => 'Название',
                'content' => function ($model) {
                    return $model->product->name . ', ' . $model->featureName . '<strong>' . ProductFeature::getPurchaseTypeProduct($model->product->id) . '</strong>';
                }
            ],
            [
                'label' => 'Цена для участников',
                'content' => function ($model) {
                    return $model->productPrices[0]->member_price;
                }
            ],
            [
                'label' => 'Цена для всех',
                'content' => function ($model) {
                    return $model->productPrices['0']->price;
                }
            ],
            [
                'label' => 'Количество',
                'content' => function ($model) {
                    return $model->quantity;
                }
            ],
            [
                'attribute' => 'visibility',
                'label' => 'Видимость',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->product->visibility ? 'checked' : '') . ' data-product-id="' . $model->product->id . '" class="update-visibility">';
                }
            ],
            [
                'attribute' => 'published',
                'label' => 'Опубликованный',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->product->published ? 'checked' : '') . ' data-product-id="' . $model->product->id . '" class="update-published">';
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-eye-open"></span>', 
                            'view?id=' . $model->product->id);
                    },
                    'update' => function ($url, $model) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-pencil"></span>', 
                            'update?id=' . $model->product->id);
                    },
                    'delete' => function ($url, $model) use ($provider) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>', 
                            'delete-provider-feature?id=' . $model->id . '&provider=' . $provider->id,
                            ['data-confirm' => 'Вы уверены что хотите удалить этот вид товара?']);
                    },
                ],
            ],
        ],
    ]); ?>
</div>