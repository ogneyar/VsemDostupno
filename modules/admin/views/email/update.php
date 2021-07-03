<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Email */

$this->title = $model->isNewRecord ? 'Добавить письмо' : 'Изменить письмо: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Письма', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Добавить' : 'Изменить';
?>
<div class="email-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
