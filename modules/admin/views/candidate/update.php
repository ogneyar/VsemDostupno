<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Candidate */

$this->title = 'Изменить кандидата: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Кандидаты', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="candidate-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'groups' => $groups
    ]) ?>

</div>
