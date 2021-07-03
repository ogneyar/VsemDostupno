<?php
$total_price = 0;
?>
<h4>Поступил заказ от "<?= $partner->name; ?>" для поставки товаров на <?= date('d.m.Y', strtotime($date)); ?></h4>

<table border="1">
    <tr>
        <th>Заказчик</th>
        <th>№ п/п</th>
        <th>Наименование товаров</th>
        <th>Количество</th>
        <th>На сумму</th>
    </tr>
    <?php $rowspan = count($details); ?>
    <?php if ($rowspan == 1): ?>
        <tr>
            <td><?= $partner->name . "<br />" . $partner->address; ?></td>
            <td><?= 1 ?></td>
            <td><?= $details[0]['product_name'] . ", " . $details[0]['product_feature_name']; ?></td>
            <td><?= number_format($details[0]['quantity']); ?></td>
            <td><?= number_format($details[0]['total'], 2, ".", " "); ?></td>
        </tr>
        <?php $total_price += $details[0]['total']; ?>
    <?php else: ?>
        <tr>
            <td rowspan="<?= $rowspan; ?>"><?= $partner->name . "<br />" . $partner->address; ?></td>
            <td><?= 1 ?></td>
            <td><?= $details[0]['product_name'] . ", " . $details[0]['product_feature_name']; ?></td>
            <td><?= number_format($details[0]['quantity']); ?></td>
            <td><?= number_format($details[0]['total'], 2, ".", " "); ?></td>
        </tr>
        <?php $total_price += $details[0]['total']; ?>
        <?php foreach ($details as $k => $detail): ?>
            <?php if ($k != 0): ?>
                <tr>
                    <td><?= $k + 1 ?></td>
                    <td><?= $detail['product_name'] . ", " . $detail['product_feature_name']; ?></td>
                    <td><?= number_format($detail['quantity']); ?></td>
                    <td><?= number_format($detail['total'], 2, ".", " "); ?></td>
                </tr>
                <?php $total_price += $detail['total']; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <tr>
        <td colspan="5" align="right"><b>ИТОГО: <?= number_format($total_price, 2, ".", ""); ?></b></td>
    </tr>
</table>