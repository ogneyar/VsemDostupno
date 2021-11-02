<?php

use yii\widgets\LinkPager;
use kartik\helpers\Html;
use app\modules\purchase\models\PurchaseProduct;

?>

<?php if ($products): ?>
    <div class="product-grid">
        <?php if (isset($pages)): ?>
            <div class="row text-right">
                <div class="col-md-12">
                    <?= LinkPager::widget([
                        'pagination' => $pages,
                    ]) ?>
                </div>
            </div>
        <?php endif ?>

        <?php for ($exCount = 0; $exCount < count($products); $exCount += 4): ?>
            <div class="row text-center">
                <?php for ($inCount = $exCount; $inCount < $exCount + 4 && $inCount < count($products); $inCount += 1): ?>
                    <?php if ($products[$inCount]->category->isPurchase()): ?>
                        <?php $date = PurchaseProduct::getClosestDateForProduct($products[$inCount]); ?>
                    <?php endif ?>
                    <div class="col-md-3 product-item">
                        
                            <div style="height: 25px;">
                                <h5 class="text-center" style="font-size: 20px;"><strong><?= (isset($date) && strtotime($date) > 0) ? 'Доставка ' . date('d.m.Yг.', strtotime($date)) : '' ?></strong></h5>
                            </div>
                       
                        <div class="row">
                            <div class="col-md-12">
                                <?= Html::a(
                                    Html::img($products[$inCount]->thumbUrl),
                                    $products[$inCount]->url,
                                    ['class' => 'thumbnail']
                                ) ?>
                            </div>
                        </div>
                        <div class="row product-name">
                            <div class="col-md-12">
                                <?= Html::tag('h5', Html::encode($products[$inCount]->name)) ?>
                            </div>
                        </div>
                        <div class="row product-price">
                            <div class="col-md-12">
                                <?php if (Yii::$app->user->isGuest): ?>
                                    <?= $products[$inCount]->productFeatures[0]->is_weights == 1 ? Html::badge(Yii::$app->formatter->asCurrency($products[$inCount]->formattedPrice * $products[$inCount]->productFeatures[0]->volume, 'RUB') , ['class' => '']) : Html::badge($products[$inCount]->formattedPrice, ['class' => '']) ?>
                                <?php else: ?>
                                    <?= $products[$inCount]->productFeatures[0]->is_weights == 1 ? Html::badge(Yii::$app->formatter->asCurrency($products[$inCount]->formattedMemberPrice * $products[$inCount]->productFeatures[0]->volume, 'RUB') , ['class' => '']) : Html::badge($products[$inCount]->formattedMemberPrice, ['class' => '']) ?>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                <?php endfor ?>
            </div>
        <?php endfor ?>

        <?php if (isset($pages)): ?>
            <div class="row text-right">
                <div class="col-md-12">
                    <?= LinkPager::widget([
                        'pagination' => $pages,
                    ]) ?>
                </div>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>
