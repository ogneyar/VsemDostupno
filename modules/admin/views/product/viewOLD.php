<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Product */

$this->title = 'Товар: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Товары', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот товар?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'visibility',
            'only_member_purchase',
            'published',
            'auto_send',
            [
                'label' => 'Поставщик',
                'value' => $model->getProviderForView(),
            ],
            [
                'label' => 'Категория',
                'value' => $model->getCategoryForView(),
            ],
            'name',
            [
                'label' => 'Имеющиеся виды',
                'value' => $model->getFeaturesForView(),
                'format' => 'raw',
            ],
            'composition',
            'packing',
            'manufacturer',
            'status',
            'description:html',
            'min_inventory',
            'expiry_timestamp',
            'thumbUrl:image',
            'thumbUrlManufacturer:image',
        ],
    ]) ?>

</div>
