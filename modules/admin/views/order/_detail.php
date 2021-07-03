<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\web\JsExpression;
use yii\bootstrap\Modal;
use kartik\dropdown\DropdownX;
use kartik\icons\Icon;
use app\models\OrderStatus;
use app\models\User;
use app\helpers\Sum;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="order-index">
    <?php foreach ($dataProvider->getModels() as $model): ?>
        <h4 style="text-decoration: underline; text-align: center;">Заявка №<?= sprintf("%'.05d\n", $model->order_id) ?> (<?= date("d.m.Yг.: H.i", strtotime($model->created_at)) ?>)</h4>
        <p style="text-decoration: underline;">Заказчик: <strong><?= empty($model->role) ? "ГОСТЬ" : "УЧАСТНИК" ?></strong> <?php if (!empty($model->role)): ?><a href="<?= Url::to(['/admin/search/search?fio=' . $model->lastname . "+" . $model->firstname . "+" . $model->patronymic]) ?>"><?= Html::encode($model->fullName) ?> № Регистрации: <?= $model->user->number ?></a><?php else: ?><?= $model->lastname . " " . $model->firstname . " " . $model->patronymic ?><?php endif; ?></p>
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
                        <td><?= $ohp->product->productFeatures[0]->is_weights == 1 ? $ohp->quantity : number_format($ohp->quantity) ?></td>
                        <td><?= $ohp->product->productFeatures[0]->measurement ?></td>
                        <td><?= $ohp->total ?></td>
                        <td>
                            <?php if ($ohp->product->productFeatures[0]->is_weights == 1): ?>
                                <a href="javascript:void(0);" id="weights-correct" class="btn btn-default" onclick="correctWeights(this);" data-pname="<?= $ohp->name ?>" data-quantity="<?= $ohp->product->productFeatures[0]->is_weights == 1 ? $ohp->quantity : number_format($ohp->quantity) ?>" data-price="<?= $ohp->price ?>" data-ohp-id="<?= $ohp->id ?>"><?= Icon::show('plus') ?></a>
                            <?php endif; ?>
                        </td>
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
                                            'onclick' => 'deleteOrderStock(this);',
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
                                            'onclick' => 'deleteReturnOrderStock(this);',
                                        ],
                                        'visible' => Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN
                                    ],
                                ],
                            ]) .
                            Html::endTag('div');
                        ?>
                        <?= Html::button('Скрыть', [
                                'type'=>'button',
                                'class'=>'btn btn-primary',
                                'style' => 'margin-top: 10px',
                                'onclick' => 'hideOrderStock(this);',
                                'data-order-id' => $model->id,
                                'data-date-e' => $date_e,
                                'data-date-s' => $date_s,
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

<?php Modal::begin([
    'id' => 'correct-weights-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Указать вес' . '</h4>',
]); ?>
    
    <table class="table table-bordered">
        <thead>
            <th>Наименование товара</th>
            <th>Цена</th>
            <th>Количество</th>
            <th>Итог</th>
        </thead>
        <tbody>
            <tr>
                <td id="pname-td"></td>
                <td id="price-td"></td>
                <td id="quantity-td"></td>
                <td id="total-td"></td>
            </tr>
        </tbody>
    </table>
    
    <label for="quantity-correct-txt">Общий вес:</label>
    <input type="text" pattern="\d+(\.\d{1,3})?" id="quantity-correct-txt" value="" style="width: 80px; padding-left: 5px;" onkeyup="setTotalCorrect()">
    <input type="hidden" id="ohp-id" value="">
    <div class="form-group" style="text-align: right;" id="correct-weight-btns">
        <?= Html::button('Отмена', ['class' => 'btn btn-default', 'data-dismiss' => 'modal', 'aria-hidden' => 'true']) ?>
        <?= Html::submitButton('Пересчитать', ['class' => 'btn btn-success', 'id' => 'correct-recalc', 'onclick' => 'correctRecalc();']) ?>
    </div>
    <div id="correct-weight-loader" style="text-align: right; display: none;">
        <img src="/images/ajax-loader.gif">
    </div>
    
<?php Modal::end(); ?>
