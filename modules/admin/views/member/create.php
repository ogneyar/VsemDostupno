<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Member */

$this->title = 'Добавить участника';
$this->params['breadcrumbs'][] = ['label' => 'Участники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
