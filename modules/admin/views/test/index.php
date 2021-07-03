<?php

use yii\helpers\Html;

use app\commands\PurchaseNotificationController;

$this->title = 'Тест';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="class">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('ТЕЕЕст', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
        // $controller = new PurchaseNotificationController(Yii::$app->controller->id, Yii::$app);
        // $controller->actionIndex();
    ?>

</div>