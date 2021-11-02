<?php

use kartik\helpers\Html;
use app\models\Category;
use app\modules\purchase\models\PurchaseProduct;

?>

<?php if ($categories): ?>
    <div class="category-panel" id="inner-cat">
        <?php if (!empty($name)): ?>
            <div class="row category-name">
                <div class="col-md-12">
                    <h2><?= Html::encode($name) ?></h2>
                </div>
            </div>
        <?php endif ?>
        <?= $this->renderFile('@app/modules/site/views/category/snippets/category-grid.php', [
            'categories' => $categories,
        ]) ?>
    </div>
<?php endif ?>

<div class="product-panel">
    <div id="main-cat-level-1" style="display: none;">
        <?= Html::pageHeader('Исходная') ?>
        <?php foreach ($menu_first_level as $item): ?>
            <div class="col-md-4">
                <?= Html::a(
                        Html::img($item->thumbUrl),
                        $item->url,
                        ['class' => 'thumbnail']
                ) ?>
                <h5 class="text-center" style="font-size: 20px;"><strong><?= $item->name ?></strong></h5>
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($menu_first_level as $f_level): ?>
        <div id="main-cat-level-2-<?= $f_level->id ?>" class="main-cat-level-2" style="display: none;">
            <?= Html::pageHeader(Html::encode($f_level->fullName)) ?>
            <?php $categories = Category::getMenuItems($f_level); ?>
            <?php if ($categories): ?>
                <?php $categories = PurchaseProduct::getSortedViewItems($categories) ?>

                <?php for ($exCount = 0; $exCount < count($categories); $exCount += 4): ?>
                <div class="row">

                    <?php for ($inCount = $exCount; $inCount < $exCount + 4 && $inCount < count($categories); $inCount += 1): ?>                      

                        <?php if ($categories[$inCount]['model']->isPurchase()): ?>
                            <?php $productsQuery = $categories[$inCount]['model']->getAllProductsQuery()
                                    ->andWhere('visibility != 0')
                                    ->andWhere('published != 0'); 
                                $products = $productsQuery->all();
                                $date = PurchaseProduct::getClosestDate($products);
                            ?>
                        <?php endif; ?>
                       

                        <div class="col-md-3">
                            <div class="purchase-date-hdr">
                                <h5 class="text-center" style="font-size: 20px;"><strong><?= (isset($date) && strtotime($date) > 0) ? 'Доставка ' . date('d.m.Yг.', strtotime($date)) : ' ' ?></strong></h5>
                            </div>
                            <?php //$target = empty($categories[$inCount]->external_link) ? '_self' : '_blank' ?>
                            <?= Html::a(
                                Html::img($categories[$inCount]['thumbUrl']),
                                //empty($categories[$inCount]->external_link) ? $categories[$inCount]->url : $categories[$inCount]->external_link,
                                $categories[$inCount]['url'],
                                ['class' => 'thumbnail', 'target' => $categories[$inCount]['options']['target']]
                            ) ?>
                            <h5 class="text-center" style="font-size: 20px;"><strong><?= $categories[$inCount]['content'] ?></strong></h5>
                        </div>


                   
                    <?php endfor ?>
                </div>
                <?php endfor ?>


                <!-- <?php //foreach ($categories as $cat): ?>
                    <?php //if ($cat['model']->isPurchase()): ?>
                        <?php /*$productsQuery = $cat['model']->getAllProductsQuery()
                                ->andWhere('visibility != 0')
                                ->andWhere('published != 0'); 
                            $products = $productsQuery->all();
                            $date = PurchaseProduct::getClosestDate($products);
                        */?>
                    <?php //endif; ?>
                    <div class="col-md-3">
                        <?php //if ($cat['model']->isPurchase()): ?>
                            <div class="purchase-date-hdr">
                                <h5 class="text-center" style="font-size: 20px;"><strong><?/*= $date ? 'Закупка ' . date('d.m.Yг.', strtotime($date)) : '' */?></strong></h5>
                            </div>
                        <?php //endif; ?>
                        <?/*= Html::a(
                                Html::img($cat['thumbUrl']),
                                $cat['url'],
                                ['class' => 'thumbnail', 'target' => $cat['options']['target']]
                        ) */?>
                        <h5 class="text-center" style="font-size: 20px;"><strong><?//= $cat['content'] ?></strong></h5>
                    </div>
                <?php // endforeach; ?>
                 -->
            <?php else: ?>
                <?php $productsQuery = $f_level->getAllProductsQuery()
                        ->andWhere('visibility != 0')
                        ->andWhere('published != 0'); 
                    $products = $productsQuery->all();
                ?>
                <?php if ($products): ?>
                    <div class="row text-center">
                        <?php foreach ($products as $val): ?>
                            <div class="col-md-3 product-item">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= Html::a(
                                            Html::img($val->thumbUrl),
                                            $val->url,
                                            ['class' => 'thumbnail']
                                        ) ?>
                                    </div>
                                </div>
                                <div class="row product-name">
                                    <div class="col-md-12">
                                        <?= Html::tag('h5', Html::encode($val->name)) ?>
                                    </div>
                                </div>
                                <div class="row product-price">
                                    <div class="col-md-12">
                                        <?php if (Yii::$app->user->isGuest): ?>
                                            <?= $val->productFeatures[0]->is_weights == 1 ? Html::badge(Yii::$app->formatter->asCurrency($val->formattedPrice * $val->productFeatures[0]->volume, 'RUB') , ['class' => '']) : Html::badge($val->formattedPrice, ['class' => '']) ?>
                                        <?php else: ?>
                                            <?= $val->productFeatures[0]->is_weights == 1 ? Html::badge(Yii::$app->formatter->asCurrency($val->formattedMemberPrice * $val->productFeatures[0]->volume, 'RUB') , ['class' => '']) : Html::badge($val->formattedMemberPrice, ['class' => '']) ?>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
