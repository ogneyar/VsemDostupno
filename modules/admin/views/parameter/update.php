<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Parameter */

$this->title = $model->isNewRecord ? 'Добавить параметр' : 'Изменить параметр: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Параметры', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Добавить' : 'Изменить';
?>
<div class="parameter-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
