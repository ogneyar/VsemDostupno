<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Email */

$this->title = 'Добавить письмо';
$this->params['breadcrumbs'][] = ['label' => 'Письма', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
