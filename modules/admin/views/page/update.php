<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Page */

$this->title = $model->isNewRecord ? 'Добавить страницу' : 'Изменить страницу: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Страницы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Добавить' : 'Изменить';
?>
<div class="page-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
