<ol>
<?php foreach ($model->purchaseOrderProducts as $k => $orderHasProduct): ?>
    <?php if ($orderHasProduct->purchaseProduct->purchase_date == $date): ?>
        <li>
            <?php if ($orderHasProduct->product): ?>
                <a href="<?= 'http://будь-здоров.рус' . $orderHasProduct->product->url ?>" target="_blank"><?= $orderHasProduct->name . ', ' . $orderHasProduct->productFeature->featureName ?></a>
            <?php else: ?>
                <?= $orderHasProduct->name ?>
            <?php endif ?>
            <?php $quantity = $orderHasProduct->purchaseProduct->is_weights ? $orderHasProduct->quantity : number_format($orderHasProduct->quantity) ?>
            <?= $quantity . ' x ' . $orderHasProduct->price . ' = ' . $orderHasProduct->total ?>
        </li>
    <?php endif; ?>
<?php endforeach ?>
</ol>