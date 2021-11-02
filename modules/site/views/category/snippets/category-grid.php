<?php

use yii\helpers\Html;

use app\modules\purchase\models\PurchaseProduct;
?>
<?php if ($categories): ?>
    <?php $categories = PurchaseProduct::getSortedView($categories) ?>
    <div class="category-grid">
        <?php for ($exCount = 0; $exCount < count($categories); $exCount += 4): ?>
            <div class="row category-item">
            <?php for ($inCount = $exCount; $inCount < $exCount + 4 && $inCount < count($categories); $inCount += 1): ?>
                <?php if ($categories[$inCount]->isPurchase()): ?>
                    <?php $productsQuery = $categories[$inCount]->getAllProductsQuery()
                            ->andWhere('visibility != 0')
                            ->andWhere('published != 0'); 
                        $products = $productsQuery->all();
                        $date = PurchaseProduct::getClosestDate($products);
                    ?>
                <?php endif; ?>
                <div class="col-md-3">
                    <div class="purchase-date-hdr">
                        <h5 class="text-center" style="font-size: 20px;"><strong><?= (isset($date) && strtotime($date) > 0) ? 'Доставка ' . date('d.m.Yг.', strtotime($date)) : '' ?></strong></h5>
                    </div>
                    <?php $target = empty($categories[$inCount]->external_link) ? '_self' : '_blank' ?>
                    <?= Html::a(
                        Html::img($categories[$inCount]->thumbUrl),
                        empty($categories[$inCount]->external_link) ? $categories[$inCount]->url : $categories[$inCount]->external_link,
                        ['class' => 'thumbnail', 'target' => $target]
                    ) ?>
                    <h5 class="text-center" style="font-size: 20px;"><strong><?= $categories[$inCount]->htmlFormattedFullName ?></strong></h5>
                </div>
            <?php endfor ?>
            </div>
        <?php endfor ?>
    </div>
<?php endif ?>
