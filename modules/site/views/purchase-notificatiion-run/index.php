<?php

use yii\helpers\Html;

use app\commands\PurchaseNotificationController;

$this->title = 'PurchaseNotificationRun';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="class">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        purchase notificatiion запустился
    </p>

    <?php
        $controller = new PurchaseNotificationController(Yii::$app->controller->id, Yii::$app);
        $controller->actionIndex();
    ?>

</div>