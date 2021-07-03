<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>

<h2>Данные заказа</h2>
<p><b>ФИО:</b> <?= Html::encode($model->fullName) ?></p>
<p><b>Город:</b> <?= Html::encode($model->city_name) ?></p>
<?php if ($model->partner_id): ?>
    <p><b>Партнер:</b> <?= Html::encode($model->partner->name) ?></p>
<?php endif ?>
<p><b>Емайл:</b> <?= Html::a($model->email, 'emailto:' . $model->email) ?></p>
<p><b>Телефон:</b> <?= Html::a($model->phone, 'tel:' . $model->phone) ?></p>
<?php if ($model->address): ?>
    <p><b>Адрес:</b> <?= Html::encode($model->address) ?></p>
<?php endif ?>
<?php if ($model->comment): ?>
    <p><b>Комментарий:</b> <?= Html::encode($model->comment) ?></p>
<?php endif ?>
<?php if ($model->user): ?>
    <p><b>Списано с лицевого счёта за заказ:</b> <?= Html::encode($model->paid_total) ?></p>
    <p><b>Остаток на лицевом счёте на текущий момент:</b> <?= Html::encode($model->user->deposit->total) ?></p>
<?php else: ?>
    <p><b>Стоимость заказа:</b> <?= Html::encode($model->formattedTotal) ?></p>
<?php endif ?>

<h2>Товары в заказе</h2>
<?php foreach ($model->orderHasProducts as $orderHasProduct): ?>
    <p>
        <?php if ($orderHasProduct->product): ?>
            <?= Html::a(Html::encode($orderHasProduct->name . ', ' . $orderHasProduct->productFeature->featureName), Url::to([$orderHasProduct->product->url], true), ['target' => '_blank']) ?>
            <?php if ($orderHasProduct->purchaseDate): ?>
                (<?= $orderHasProduct->htmlFormattedPurchaseDate ?>)
            <?php endif ?>
        <?php else: ?>
            <?= Html::encode($orderHasProduct->name) ?>
        <?php endif ?>
        <?= Html::encode($orderHasProduct->quantity . ' x ' . $orderHasProduct->formattedPrice . ' = ' . $orderHasProduct->formattedTotal) ?>
    </p>
<?php endforeach ?>
