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

$this->title = 'История моих закупок на ' . date("d.m.Yг.", strtotime($date));
?>
<?= Html::pageHeader(Html::encode($this->title)) ?>
<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <?php $total = 0; $k = 1; ?>
        <h4 style="text-decoration: underline; text-align: center;">
            Заявка № <?= !empty($model->order_id) ? sprintf("%'.05d\n", $model->order_id) : $model->order_number ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)
            <p><?= $model->textStatus ?></p>
        </h4>
        <table class="table table-bordered">
            <thead>
                <th style="width: 57px;">№ п/п</th>
                <th style="width: 650px;">Наименование товара</th>
                <th style="width: 65px;">Цена</th>
                <th>Кол-во</th>
                <th>Ед. измер.</th>
                <th>Сумма</th>
                <th></th>
            </thead>
            <tbody>
                <?php foreach ($model->purchaseOrderProducts as $ohp): ?>
                    <?php if ($ohp->purchaseProduct->purchase_date == $date): ?>
                        <tr>
                            <td><?= $k ++ ?></td>
                            <td><?= $ohp->name ?></td>
                            <td><?= $ohp->price ?></td>
                            <td><?= number_format($ohp->quantity) ?></td>
                            <td><?= $ohp->product->productFeatures[0]->measurement ?></td>
                            <td><?= $ohp->total ?></td>
                        </tr>
                        <?php $total += $ohp->total ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="5"><span style="text-decoration: underline;">Списано с лицевого счёта заказчика:</span> <strong><?= Sum::toStr($total) ?></strong></td>
                    <td><strong><?= number_format($total, 2, '.', '') ?></strong></td>
                    <td rowspan="2">
                        <?php if ($model->status == 'held' || $model->status == 'part_held'): ?>
                            <?= Html::beginTag('div', ['class'=>'dropdown']) .
                                    Html::button('Действия <span class="caret"></span>', [
                                        'type'=>'button',
                                        'class'=>'btn btn-default',
                                        'data-toggle'=>'dropdown'
                                    ]) .
                                    DropdownX::widget([
                                    'items' => [
                                        [
                                            'label' => 'Прих. ордер',
                                            'url' => Url::to(['/admin/order/download-order', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Акт возврата',
                                            'url' => Url::to(['/admin/order/download-act', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Заявка',
                                            'url' => Url::to(['/admin/order/download-request', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Акт возврата паевого взноса',
                                            'url' => Url::to(['/admin/order/download-return-fee-act', 'id' => $model->id]),
                                        ],
                                        '<li class="divider"></li>',
                                        
                                    ],
                                ]) .
                                Html::endTag('div');
                            ?>
                        <?php endif; ?>
                        <?php if ($model->status == 'abortive' || $model->status == 'part_abortive' || $model->status == 'completed'): ?>
                            <?= Html::a('Повторить заказ', Url::to(['reorder', 'id' => $model->id, 'date' => $date]), [
                                    'class' => 'btn btn-success',
                                    'style' => 'margin-top: 10px',
                                    'data-pjax' => 0,
                                    'data-method' => "post",
                                    'data-confirm' => "Желаете повторить этот заказ?"
                                ]);
                            ?>
                            <?= Html::a('Удалить', Url::to(['delete', 'id' => $model->id, 'date' => $date]), [
                                    'class' => 'btn btn-danger',
                                    'style' => 'margin-top: 10px',
                                    'data-pjax' => 0,
                                    'data-method' => "post",
                                    'data-confirm' => "Вы уверены, что хотите удалить заказ?"
                                ]);
                            ?>
                        <?php endif; ?>
                    </td>
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
