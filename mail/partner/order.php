<?php
$models = $dataProvider->getModels();
$total_price = 0;
?>

<h4>Заявка на поставку товаров на <?= date('d.m.Y', strtotime($date)); ?></h4>
<table border="1">
    <tr>
        <th>№ п/п</th>
        <th>Наименование товаров</th>
        <th>Поставщик</th>
        <th>Количество</th>
        <th>На сумму</th>
    </tr>
    <?php foreach ($models as $k => $rec): ?>
        <tr>
            <td><?= $k + 1; ?></td>
            <td><?= $rec['product_name'] . ', ' . $rec['product_feature_name']; ?></td>
            <td><?= $rec['provider_name']; ?></td>
            <td><?= number_format($rec['quantity']); ?></td>
            <td><?= $rec['total']; ?></td>
        </tr>
        <?php $total_price += $rec['total']; ?>
    <?php endforeach; ?>
    <tr>
        <td colspan="4">ИТОГО:</td>
        <td><?= number_format($total_price, 2, ".", ""); ?></td>
    </tr>
</table>