<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\OrderStatus */

$this->title = $model->isNewRecord ? 'Добавить статус заказа' : 'Изменить статус заказа: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Статусы заказов', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Добавить' : 'Изменить';
?>
<div class="order-status-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
