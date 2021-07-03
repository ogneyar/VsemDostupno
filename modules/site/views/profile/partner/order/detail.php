<?php
use kartik\helpers\Html;
use yii\helpers\Url;
use app\models\ProductFeature;

$this->title = 'Детали заказа';
$this->params['breadcrumbs'][] = ['label' => 'Заказы на склад', 'url' => '/profile/partner/order/index'];
$this->params['breadcrumbs'][] = $this->title;
$total_price = $total_qnt = 0;
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>
<h4>Заказы от участников <?= $partner->name; ?> на <?= date('d.m.Y', strtotime($date)); ?></h4>
<div class="order-index">
    <table class="table table-bordered">
        <thead>
            <th>Поставщик</th>
            <th>Наименование товаров</th>
            <th>№ п/п</th>
            <th>Ф.И.О. участников заказавших товар</th>
            <th>№ заявки</th>
            <th>Вид</th>
            <th>Цена за ед. товара</th>
            <th>Количество</th>
            <th>На сумму</th>
        </thead>
        <tbody>
            <?php $rowspan = count($details); ?>
            <?php if ($rowspan == 1): ?>
                <tr>
                    <td><?= $provider->name; ?></td>
                    <td><?= $details[0]['name']; ?></td>
                    <td><?= 1; ?></td>
                    <td><?= $details[0]['fio']; ?></td>
                    <td><?= sprintf("%'.05d\n", $details[0]['id']); ?></td>
                    <td><?= ProductFeature::getFeatureNameById($details[0]['product_feature_id']); ?></td>
                    <td><?= $details[0]['price']; ?></td>
                    <td><?= ProductFeature::isWeights($details[0]['product_feature_id']) ? $details[0]['quantity'] : number_format($details[0]['quantity']) ?></td>
                    <td><b><?= $details[0]['total']; ?></b></td>
                </tr>
                <?php $total_price += $details[0]['total']; ?>
                <?php $total_qnt += $details[0]['quantity']; ?>
            <?php else: ?>
                <tr>
                    <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $provider->name; ?></td>
                    <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= $details[0]['name']; ?></td>
                    <td><?= 1; ?></td>
                    <td><?= $details[0]['fio']; ?></td>
                    <td><?= sprintf("%'.05d\n", $details[0]['id']); ?></td>
                    <td><?= ProductFeature::getFeatureNameById($details[0]['product_feature_id']); ?></td>
                    <td><?= $details[0]['price']; ?></td>
                    <td><?= ProductFeature::isWeights($details[0]['product_feature_id']) ? $details[0]['quantity'] : number_format($details[0]['quantity']) ?></td>
                    <td><b><?= $details[0]['total']; ?></b></td>
                </tr>
                <?php $total_price += $details[0]['total']; ?>
                <?php $total_qnt += $details[0]['quantity']; ?>
                <?php foreach ($details as $k => $detail): ?>
                    <?php if ($k != 0): ?>
                        <tr>
                            <td><?= $k + 1 ?></td>
                            <td><?= $detail['fio']; ?></td>
                            <td><?= sprintf("%'.05d\n", $details[0]['id']); ?></td>
                            <td><?= ProductFeature::getFeatureNameById($detail['product_feature_id']); ?></td>
                            <td><?= $detail['price']; ?></td>
                            <td><?= ProductFeature::isWeights($detail['product_feature_id']) ? $detail['quantity'] : number_format($detail['quantity']) ?></td>
                            <td><b><?= $detail['total']; ?></b></td>
                        </tr>
                        <?php $total_price += $detail['total']; ?>
                        <?php $total_qnt += $detail['quantity']; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <td colspan="7"><b>ИТОГО:</b></td>
            <td><?= $total_qnt; ?></td>
            <td><b><?= number_format($total_price, 2, ".", ""); ?></b></td>
        </tfoot>
    </table>
</div>