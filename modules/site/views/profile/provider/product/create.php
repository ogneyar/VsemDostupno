<?php

use yii\helpers\Url;
use kartik\helpers\Html;

/* @var $this yii\web\View */
$this->title = 'Добавить товар';
$this->params['breadcrumbs'][] = ['label' => 'Мои товары', 'url' => Url::to(['/profile/provider/product'])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-create">
    <?= Html::pageHeader(Html::encode($this->title)) ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
