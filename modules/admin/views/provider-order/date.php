<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Order;
use app\models\ProductFeature;
use app\models\ProviderNotification;
use app\models\Provider;
use app\models\User;

use app\modules\purchase\models\PurchaseOrder;
    
$this->title = 'Заявка на поставку товаров на ' . date('d.m.Y', strtotime($date));
$this->params['breadcrumbs'][] = ['label' => 'Коллективная закупка', 'url' => '/web/admin/provider-order'];
$this->params['breadcrumbs'][] = $this->title;

$model = $dataProvider->getModels();
$total_price = 0;
$delete_action = Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? 'delete' : 'admin-delete';
$script = <<<JS
$(function () {
    setPageView();
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="purchase-date">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <table class="table table-bordered">
        <thead>
            <th style="vertical-align: top;">№ п/п</th>
            <th style="vertical-align: top;">Наименование товаров</th>
            <th style="vertical-align: top;">Поставщик</th>
            <th style="vertical-align: top;">Наименование группы (заказчик)</th>
            <th style="vertical-align: top;">Количество</th>
            <th style="vertical-align: top;">На сумму</th>
            <th style="vertical-align: top;">Ед. измерения</th>
            <th style="vertical-align: top;">Общее количество</th>
            <th style="vertical-align: top;">На общую сумму</th>
            <th style="vertical-align: top;">Поставщик уведомлен</th>
        </thead>
        <tbody>
            <?php foreach ($model as $k => $val): ?>
                <?php $orders = PurchaseOrder::getOrderByProduct($val['product_feature_id'], $date); ?>
                <?php $rowspan = count($orders); ?>
                <?php if ($rowspan == 1): ?>
                    <tr>
                        <td><?= $k + 1; ?></td>
                        <td><?= $val['product_name']; ?></td>
                        <td><?= $val['provider_name']; ?></td>
                        <td><?= $orders[0]['p_name']; ?></td>
                        <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $orders[0]['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($date))]); ?>" style="text-decoration: underline;"><?= number_format($orders[0]['quantity']); ?></a></td>
                        <td><b><?= $orders[0]['total']; ?></b></td>
                        <td><?= ProductFeature::getFeatureNameById($val['product_feature_id']); ?></td>
                        <td><?= number_format($val['total_qnt']); ?></td>
                        <td><b><?= $val['total_price']; ?></b></td>
                        <td>
                            <?php if (ProviderNotification::find()->where(['order_date' => date('Y-m-d', strtotime($date)), 'provider_id' => $val['provider_id'], 'product_id' => $val['id']])->exists()): ?>
                                Да
                            <?php else: ?>
                                Нет<br />
                                <?php $provider = Provider::find()->where(['id' => $val['provider_id']])->with('user')->one(); ?>
                                <?= $provider ? $provider->user->phone : ''; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $k + 1; ?></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $val['product_name']; ?></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $val['provider_name']; ?></td>
                        <td><?= $orders[0]['p_name']; ?></td>
                        <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $orders[0]['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($date))]); ?>" style="text-decoration: underline;"><?= number_format($orders[0]['quantity']); ?></a></td>
                        <td><b><?= $orders[0]['total']; ?></b></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= ProductFeature::getFeatureNameById($val['product_feature_id']); ?></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= number_format($val['total_qnt']); ?></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align"><b><?= $val['total_price']; ?></b></td>
                        <td rowspan="<?= $rowspan; ?>" class="td-v-align">
                            <?php if (ProviderNotification::find()->where(['order_date' => date('Y-m-d', strtotime($date)), 'provider_id' => $val['provider_id'], 'product_id' => $val['id']])->exists()): ?>
                                Да
                            <?php else: ?>
                                Нет
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php foreach ($orders as $y => $order): ?>
                        <?php if ($y != 0): ?>
                            <tr>
                                <td><?= $order['p_name']; ?></td>
                                <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $order['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($date))]); ?>" style="text-decoration: underline;"><?= number_format($order['quantity']); ?></a></td>
                                <td><b><?= $order['total']; ?></b></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php $total_price += $val['total_price']; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <td colspan="8" class="td-foot-total">ИТОГО:</td>
            <td colspan="2"><b><?= number_format($total_price, 2, ".", ""); ?></b></td>
        </tfoot>
    </table>
    
    <?= Html::a('Детализация', 'javascript:void(0)', ['class' => 'btn btn-success closed', 'onclick' => 'detalization();', 'id' => 'purchase-details-btn']) ?>
    <?= Html::a('Удалить', Url::to([$delete_action, 'date' => date('Y-m-d', strtotime($date))]), [
                'class' => 'btn btn-danger pull-right',
                'data-pjax' => 0,
                'data-method' => "post",
                'data-confirm' => "Вы уверены что хотите удалить закупку?"
            ]) ?>
    <input type="hidden" id="details-date" value="<?= $date; ?>">
    <div id="purchase-details-container" class="purchase-details" style="display: none;"></div>
</div> 