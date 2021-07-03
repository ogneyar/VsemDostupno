<?php

use kartik\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
$this->title = 'Личные данные';
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<p style="font-size: 28px;"><?= $user_data['firstname'] . ' ' . $user_data['patronymic']; ?>, Ваш номер регистрации №<?= $user_data['number']; ?></p>