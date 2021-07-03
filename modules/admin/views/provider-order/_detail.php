<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\web\JsExpression;
use kartik\dropdown\DropdownX;
use app\models\OrderStatus;
use app\models\User;
use app\helpers\Sum;

?>
<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <?php $total = 0; $k = 1; ?>
        <h4 style="text-decoration: underline; text-align: center;">Заявка № <?= !empty($model->order_id) ? sprintf("%'.05d\n", $model->order_id) : $model->order_number ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)</h4>
        <p style="text-decoration: underline;">Заказчик: <strong><?= empty($model->role) ? "ГОСТЬ" : "УЧАСТНИК" ?></strong> <?php if (!empty($model->role)): ?><a href="<?= Url::to(['/admin/search/search?fio=' . $model->lastname . "+" . $model->firstname . "+" . $model->patronymic]) ?>"><?= Html::encode($model->fullName) ?> № Регистрации: <?= $model->user->number ?></a><?php else: ?><?= $model->lastname . " " . $model->firstname . " " . $model->patronymic ?><?php endif; ?></p>
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
                    <td rowspan="2" width="10%">
                        <?php if (!empty($model->order_id)): ?>
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
                                            'url' => Url::to(['/admin/purchase/download-order', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Акт возврата',
                                            'url' => Url::to(['/admin/purchase/download-act', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Заявка',
                                            'url' => Url::to(['/admin/purchase/download-request', 'id' => $model->id]),
                                        ],
                                        [
                                            'label' => 'Акт возврата паевого взноса',
                                            'url' => Url::to(['/admin/purchase/download-return-fee-act', 'id' => $model->id]),
                                        ],
                                        '<li class="divider"></li>',
                                        [
                                            'label' => 'Удалить',
                                            'url' => 'javascript:void(0)',
                                            'linkOptions' => [
                                                'data' => [
                                                    'order-id' => $model->id
                                                ],
                                                'onclick' => 'deleteOrder(this);',
                                            ],
                                            'visible' => Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN
                                        ],
                                        [
                                            'label' => 'Сделать возврат и удалить',
                                            'url' => 'javascript:void(0)',
                                            'linkOptions' => [
                                                'data' => [
                                                    'order-id' => $model->id
                                                ],
                                                'onclick' => 'deleteReturnOrder(this);',
                                            ],
                                            'visible' => Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN
                                        ],
                                    ],
                                ]) .
                                Html::endTag('div');
                            ?>
                        <?php endif; ?>
                        
                        <?php if (empty($model->role) && !$model->purchaseOrderProducts[0]->purchaseFundBalances[0]->paid): ?>
                            <?= Html::button('Расчёт', [
                                    'type'=>'button',
                                    'class'=>'btn btn-primary',
                                    'style' => 'margin-top: 10px',
                                    'onclick' => 'payFund(this);',
                                    'data-order-id' => $model->id,
                                    'data-date' => $date,
                                ]);
                            ?>
                        <?php endif; ?>
                        <?= Html::button('Скрыть', [
                                'type'=>'button',
                                'class'=>'btn btn-primary',
                                'style' => 'margin-top: 10px',
                                'onclick' => 'hideOrder(this);',
                                'data-order-id' => $model->id,
                                'data-date' => $date,
                            ]);
                        ?>
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
