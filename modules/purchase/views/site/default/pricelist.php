<?php

use yii\helpers\Html;

?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>Прайс-лист товаров</title>
    </head>
    <body>
        <table border="1" cellspacing="0" cellpadding="5" width="100%">
            <thead>
                <tr>
                    <th>НАЗВАНИЕ</th>
                    <th>ЗАКУПКА</th>
                    <th>РОЗНИЦА</th>
                    <th>УЧАСТНИКИ</th>
                </tr>
            </thead>
            <tbody>
                <?php $ar = []; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['name'] . $product['p_name'] . $product['descr']; ?></td>
                        <td align="center"><?= $product['date']; ?></td>
                        <td align="center"><?= $product['price']; ?></td>
                        <td align="center"><?= $product['member_price']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
</html>
