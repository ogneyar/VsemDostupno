<?php
use yii\helpers\Html;
use app\models\Order;
use app\models\ProviderNotification;
use app\models\Provider;

$models = $dataProvider->getModels();
$total_price = 0;

?>
<h4>Заявка на поставку товаров на <?= date('d.m.Y', strtotime($date)); ?></h4>
<table border="1">
    <tr>
        <td>№ п/п</td>
        <td>Наименование товаров</td>
        <td>Поставщик</td>
        <td>Наименование группы (заказчик)</td>
        <td>Количество</td>
        <td>На сумму</td>
        <td>Ед. измерения</td>
        <td>Общее количество</td>
        <td>На общую сумму</td>
        <td>Поставщик уведомлен</td>
    </tr>
    <?php foreach ($models as $k => $val): ?>
        <?php $orders = Order::getOrderByProduct($val['product_feature_id'], $date); ?>
        <?php $rowspan = count($orders); ?>
        <?php if ($rowspan == 1): ?>
            <tr>
                <td><?= $k + 1; ?></td>
                <td><?= $val['product_name']; ?></td>
                <td><?= $val['provider_name']; ?></td>
                <td><?= $orders[0]['p_name']; ?></td>
                <td><?= number_format($orders[0]['quantity']); ?></td>
                <td><b><?= $orders[0]['total']; ?></b></td>
                <td><?= $val['product_feature_name']; ?></td>
                <td><?= number_format($val['total_qnt']); ?></td>
                <td><b><?= $val['total_price']; ?></b></td>
                <td>
                    <?php if (ProviderNotification::find()->where(['order_date' => date('Y-m-d', strtotime($date)), 'provider_id' => $val['provider_id'], 'product_id' => $val['id']])->exists()): ?>
                        Да
                    <?php else: ?>
                        Нет<br />
                        <?php $provider = Provider::find()->where(['id' => $val['provider_id']])->with('user')->one(); ?>
                        <?= $provider->user->phone; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $k + 1; ?></td>
                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $val['product_name']; ?></td>
                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $val['provider_name']; ?></td>
                <td><?= $orders[0]['p_name']; ?></td>
                <td><?= number_format($orders[0]['quantity']); ?></td>
                <td><b><?= $orders[0]['total']; ?></b></td>
                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $val['product_feature_name']; ?></td>
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
                        <td><?= number_format($order['quantity']); ?></td>
                        <td><b><?= $order['total']; ?></b></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php $total_price += $val['total_price']; ?>
    <?php endforeach; ?>
    <tr>
        <td colspan="8" class="td-foot-total">ИТОГО:</td>
        <td colspan="2"><b><?= number_format($total_price, 2, ".", ""); ?></b></td>
    </tr>
</table>

<a href="<?= $link ?>">Посмотреть в админ панели</a>