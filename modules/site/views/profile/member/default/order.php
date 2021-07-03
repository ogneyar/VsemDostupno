<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;
use app\models\OrderStatus;
use app\models\User;
use app\helpers\Sum;

/* @var $this yii\web\View */
$this->title = $title;
$this->params['breadcrumbs'] = [$this->title];

?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <h4 style="text-decoration: underline; text-align: center;">Заявка №<?= sprintf("%'.05d\n", $model->order_id) ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)</h4>
        <table class="table table-bordered">
            <thead>
                <th style="width: 57px;">№ п/п</th>
                <th style="width: 650px;">Наименование товара</th>
                <th style="width: 65px;">Цена</th>
                <th>Кол-во</th>
                <th>Ед. измер.</th>
                <th>Сумма</th>
            </thead>
            <tbody>
                <?php foreach ($model->orderHasProducts as $k => $ohp): ?>
                    <tr>
                        <td><?= $k + 1 ?></td>
                        <td><?= $ohp->name ?></td>
                        <td><?= $ohp->price ?></td>
                        <td><?= number_format($ohp->quantity) ?></td>
                        <td><?= $ohp->product->productFeatures[0]->measurement ?></td>
                        <td><?= $ohp->total ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5"><span style="text-decoration: underline;">Списано с лицевого счёта заказчика:</span> <strong><?= Sum::toStr($model->total) ?></strong></td>
                    <td><strong><?= $model->total ?></strong></td>
                    
                </tr>
                <tr>
                    <td colspan="6">
                        <strong>Партнёр: </strong><span style="text-decoration: underline;"><?= !empty($model->partner_name) ? $model->partner->name : (isset($model->user->partner) ? $model->user->partner->name : "") ?></span><br>
                        <strong>Адрес доставки: </strong><span style="text-decoration: underline;"><?= isset($model->partner) ? $model->partner->address : (isset($model->user->partner) ? $model->user->partner->address : "") ?></span><br>
                        <strong>Удобное время получения заказа: </strong><span style="text-decoration: underline;"></span><br>
                        <strong>Комментарий: </strong><span style="text-decoration: underline;"><?= $model->comment ?></span><br>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
