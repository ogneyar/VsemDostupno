<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
$this->title = 'Письма';
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<p style="font-size: 28px;"><?= $user_data['firstname'] . ' ' . $user_data['patronymic']; ?>, Ваш номер регистрации №<?= $user_data['number']; ?></p>