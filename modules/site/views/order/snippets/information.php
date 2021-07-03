<?php

use yii\helpers\Url;
use kartik\helpers\Html;
use app\models\User;

?>

<table class="table table-hover table-bordered">
    <tbody>
        <tr>
            <td><b>ФИО:</b></td>
            <td><?= Html::encode($model->fullName) ?></td>
        </tr>
        <?php if (!Yii::$app->user->isGuest && !in_array(Yii::$app->user->identity->role, [User::ROLE_PARTNER])): ?>
            <tr>
                <td><b>Город:</b></td>
                <td><?= Html::encode($model->city_name) ?></td>
            </tr>
            <tr>
                <td><b>Партнер:</b></td>
                <td><?= Html::encode($model->partnerName) ?></td>
            </tr>
        <?php endif ?>
        <tr>
            <td><b>Емайл:</b></td>
            <td><?= Html::a($model->email, 'emailto:' . $model->email) ?></td>
        </tr>
        <tr>
            <td><b>Телефон:</b></td>
            <td><?= Html::a($model->phone, 'tel:' . $model->phone) ?></td>
        </tr>
        <?php if (!in_array($model->role, [User::ROLE_PARTNER]) && $model->address): ?>
            <tr>
                <td><b>Адрес:</b></td>
                <td><?= Html::encode($model->address) ?></td>
            </tr>
        <?php endif ?>
        <?php if ($model->comment): ?>
            <tr>
                <td><b>Комментарий:</b></td>
                <td><?= Html::encode($model->comment) ?></td>
            </tr>
        <?php endif ?>
    </tbody>
</table>

<table class="table table-hover table-bordered">
    <tbody>
        <?php foreach ($model->orderHasProducts as $orderHasProduct): ?>
            <tr>
                <td>
                    <?php if ($orderHasProduct->product): ?>
                        <?php if ($orderHasProduct->purchaseDate): ?>
                            <?= Html::badge($orderHasProduct->htmlFormattedPurchaseDate) ?>
                        <?php endif ?>
                        <?//= Html::a(Html::encode($orderHasProduct->name . ', ' . $orderHasProduct->productFeature->featureName), Url::to([$orderHasProduct->product->url]), ['target' => '_blank']) ?>
                    <?php else: ?>
                        <?= Html::encode($orderHasProduct->name) ?>
                    <?php endif ?>
                </td>
                <td><?= Html::encode($orderHasProduct->quantity . ' x ' . $orderHasProduct->formattedPrice . ' = ' . $orderHasProduct->formattedTotal) ?></td>
            </tr>
        <?php endforeach ?>
        <?php if ($model->user): ?>
            <tr>
                <td><b>Списано с лицевого счёта за заказ:</b></td>
                <td><?= Html::encode($model->paid_total) ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td><b>Стоимость заказа:</b></td>
                <td><?= Html::encode($model->formattedTotal) ?></td>
            </tr>
        <?php endif ?>
    </tbody>
</table>
