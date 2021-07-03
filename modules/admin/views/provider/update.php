<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */

$this->title = $model->isNewRecord ? 'Добавить поставщика' : 'Изменить поставщика: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Добавить' : 'Изменить';
?>
<div class="provider-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
