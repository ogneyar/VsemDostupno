<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Parameter */

$this->title = 'Добавить парамет';
$this->params['breadcrumbs'][] = ['label' => 'Параметры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="parameter-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
