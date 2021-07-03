<?php

use yii\widgets\LinkPager;
use kartik\helpers\Html;

?>

<?php if ($services): ?>
    <div class="service-grid">
        <?php if (isset($pages)): ?>
            <div class="row text-right">
                <div class="col-md-12">
                    <?= LinkPager::widget([
                        'pagination' => $pages,
                    ]) ?>
                </div>
            </div>
        <?php endif ?>

        <?php for ($exCount = 0; $exCount < count($services); $exCount += 4): ?>
            <div class="row text-center">
                <?php for ($inCount = $exCount; $inCount < $exCount + 4 && $inCount < count($services); $inCount += 1): ?>
                    <div class="col-md-3 service-item">
                        <div class="row">
                            <div class="col-md-12">
                                <?= Html::a(
                                    Html::img($services[$inCount]->thumbUrl),
                                    $services[$inCount]->url,
                                    ['class' => 'thumbnail']
                                ) ?>
                            </div>
                        </div>
                        <div class="row service-name">
                            <div class="col-md-12">
                                <?= Html::tag('h5', Html::encode($services[$inCount]->name)) ?>
                            </div>
                        </div>
                        <div class="row service-price">
                            <div class="col-md-12">
                                <?php if ($services[$inCount]->calculatedPrice > 0): ?>
                                    <?= Html::badge($services[$inCount]->formattedCalculatedPrice, ['class' => '']) ?>
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
