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

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<?php if (count($orders)): ?>
<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <h4 style="text-decoration: underline; text-align: center;">Заявка №<?= sprintf("%'.05d\n", $model->order_id) ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)</h4>
        <p style="text-decoration: underline;">Заказчик: <strong><?= empty($model->role) ? "ГОСТЬ" : "УЧАСТНИК" ?></strong> <a href="<?= isset($model->user->member) ? Url::to(['/admin/member/view', 'id' => $model->user->member->id]) : (isset($model->user->partner) ? Url::to(['/admin/partner/view', 'id' => $model->user->partner->id]) : "") ?>"><?= Html::encode($model->fullName) ?> № Регистрации: <?= $model->user->number ?></a></p>
        <table class="table table-bordered">
            <thead>
                <th style="width: 57px;">№ п/п</th>
                <th style="width: 650px;">Наименование товара</th>
                <th style="width: 65px;">Цена</th>
                <th>Кол-во</th>
                <th>Ед. измер.</th>
                <th>Сумма</th>
                <th>Вес</th>
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
                    <td rowspan="2">
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
<?php elseif (count($purchases)): ?>
<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <h4 style="text-decoration: underline; text-align: center;">Заявка № <?= !empty($model->order_id) ? sprintf("%'.05d\n", $model->order_id) : $model->order_number ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)</h4>
        <p style="text-decoration: underline;">Заказчик: <strong><?= empty($model->role) ? "ГОСТЬ" : "УЧАСТНИК" ?></strong> <a href="<?= isset($model->user->member) ? Url::to(['/admin/member/view', 'id' => $model->user->member->id]) : (isset($model->user->partner) ? Url::to(['/admin/partner/view', 'id' => $model->user->partner->id]) : "") ?>"><?= Html::encode($model->fullName) ?> № Регистрации: <?= $model->user->number ?></a></p>
        <table class="table table-bordered">
            <thead>
                <th style="width: 57px;">№ п/п</th>
                <th style="width: 650px;">Наименование товара</th>
                <th style="width: 65px;">Цена</th>
                <th>Кол-во</th>
                <th>Ед. измер.</th>
                <th>Сумма</th>
                <th>Вес</th>
            </thead>
            <tbody>
                <?php foreach ($model->purchaseOrderProducts as $k => $ohp): ?>
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
                    <td rowspan="2">
                        <?php if ($model->status !== 'advance'): ?>
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
<?php endif; ?>