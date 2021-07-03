<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $message;
?>
<div class="site-error">

    <h1><?= Html::encode($exception->statusCode . ': ' . $this->title) ?></h1>

    <p>
        Вы можете перейти на <?= Html::a('главную', Yii::$app->homeUrl)?> страницу и поискать нужную информацию там.
    </p>
</div>
