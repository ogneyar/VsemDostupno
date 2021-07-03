<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\Order;
use app\models\ProviderNotification;
use app\models\Provider;
use app\models\ProductFeature;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $dataProvider1 yii\data\ActiveDataProvider */
$this->title = 'Заказы поставщикам';
$this->params['breadcrumbs'][] = $this->title;
//$models = $dataProvider->getModels();
//$total_price = 0;

/*echo '<pre>';
var_dump($test);
die();*/
?>
<div class="member-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php foreach ($dataProviderAll as $i => $dataProvider): ?>
        <?php $models = $dataProvider->getModels(); ?>
        <?php if (count($models) > 0): ?>
            <?php $total_price = 0; ?>
            <h4>Заявка на поставку товаров на <?= date('d.m.Y', strtotime($dates[$i]['end'])); ?></h4>
            <a href="<?= Url::to(['/admin/provider-order/hide', 'date_e' => date('Y-m-d', strtotime($dates[$i]['end'])), 'date_s' => date('Y-m-d', strtotime($dates[$i]['start']))]); ?>">Удалить заявку</a>
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
                    <?php foreach ($models as $k => $val): ?>
                        <?php $orders = Order::getOrderByProduct($val['product_feature_id'], $dates[$i]); ?>
                        <?php $rowspan = count($orders); ?>
                        <?php if ($rowspan == 1): ?>
                            <tr>
                                <td><?= $k + 1; ?></td>
                                <td><?= $val['product_name']; ?></td>
                                <td><?= $val['provider_name']; ?></td>
                                <td><?= $orders[0]['p_name']; ?></td>
                                <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $orders[0]['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($dates[$i]['end']))]); ?>" style="text-decoration: underline;"><?= number_format($orders[0]['quantity']); ?></a></td>
                                <td><b><?= $orders[0]['total']; ?></b></td>
                                <td><?= ProductFeature::getFeatureNameById($val['product_feature_id']); ?></td>
                                <td><?= number_format($val['total_qnt']); ?></td>
                                <td><b><?= $val['total_price']; ?></b></td>
                                <td>
                                    <?php if (ProviderNotification::find()->where(['order_date' => date('Y-m-d', strtotime($dates[$i]['end'])), 'provider_id' => $val['provider_id'], 'product_id' => $val['id']])->exists()): ?>
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
                                <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $orders[0]['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($dates[$i]['end']))]); ?>" style="text-decoration: underline;"><?= number_format($orders[0]['quantity']); ?></a></td>
                                <td><b><?= $orders[0]['total']; ?></b></td>
                                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= ProductFeature::getFeatureNameById($val['product_feature_id']); ?></td>
                                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><?= number_format($val['total_qnt']); ?></td>
                                <td rowspan="<?= $rowspan; ?>" class="td-v-align"><b><?= $val['total_price']; ?></b></td>
                                <td rowspan="<?= $rowspan; ?>" class="td-v-align">
                                    <?php if (ProviderNotification::find()->where(['order_date' => date('Y-m-d', strtotime($dates[$i]['end'])), 'provider_id' => $val['provider_id'], 'product_id' => $val['id']])->exists()): ?>
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
                                        <td><a href="<?= Url::to(['/admin/provider-order/detail', 'id' => $val['product_feature_id'], 'pid' => $order['p_id'], 'prid' => $val['provider_id'], 'date' => date('Y-m-d', strtotime($dates[$i]['end']))]); ?>" style="text-decoration: underline;"><?= number_format($order['quantity']); ?></a></td>
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
        <?php endif; ?>
    <?php endforeach; ?>
</div>