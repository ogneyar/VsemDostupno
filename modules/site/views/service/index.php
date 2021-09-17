<?php

use yii\web\View;
use yii\bootstrap\Alert;
use yii\bootstrap\Modal;
use yii\web\JsExpression;
use yii\helpers\Url;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\models\Category;
use dosamigos\gallery\Gallery;
use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = $model->name;

$category = null;
$url = Yii::$app->request->referrer;
if (preg_match('/\/category\/\d+$/', $url)) {
    $categoryId = preg_replace('/^\D+/', '', $url);
    $category = Category::find()
        ->where('visibility != 0 AND id = :id', [':id' => $categoryId])
        ->one();
    if ($category) {
        $this->params['breadcrumbs'] = $category->getBreadcrumbs($model->name);
    }
}
if (!$category && $model->categories) {
    $category = $model->categories[0];
    $this->params['breadcrumbs'] = $category->getBreadcrumbs($model->name);
}

$serviceImages = [];
foreach ($model->serviceHasPhoto as $serviceHasPhoto) {
    $serviceImages[] = [
        'url' => $serviceHasPhoto->imageUrl,
        'src' => $model->thumbUrl,
        'options' => ['class' => 'hidden'],
    ];
}

?>
<div id="inner-cat">
<?= Html::pageHeader(Html::encode($model->name)) ?>

<div class="row">
    <div class="col-md-6">
        <?= Gallery::widget(['id' => 'service-images', 'items' => $serviceImages]) ?>
        <a 
            href="#" 
            class="thumbnail"
            title="Посмотреть все фотографии работ мастера"
            onclick="<?= new JsExpression('$(`#service-images a`).first().trigger(`click`);return false;') ?>"
        >
            <img src="<?=$model->thumbUrl?>" />
            <div style="background:white;color:black;font-size:20px;position:absolute;bottom:40px;left:30px;padding:0 5px;">Посмотреть все фотографии работ мастера</div>
        </a>
        <!-- <?/*= Html::a(
                Html::img($model->thumbUrl),
                '#',
                [
                    'class' => 'thumbnail',
                    'title' => 'Посмотреть все фотографии работ мастера',
                    'onclick' => new JsExpression('
                        $("#service-images a").first().trigger("click");
                        return false;
                    '),
                ]
        ) */?> -->
    </div>
    <?php if ($model->calculatedPrice > 0): ?>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <?php
                        $prices = [
                            [
                                'content' => 'Цена для всех желающих',
                                'badge' => $model->formattedPrice,
                                'options' => ['class' => $model->price != $model->calculatedPrice ? 'disabled' : ''],
                            ],
                            [
                                'content' => 'Цена для участников ПО',
                                'badge' => $model->formattedMemberPrice,
                                'options' => ['class' => $model->member_price != $model->calculatedPrice ? 'disabled' : ''],
                            ],
                        ];

                        echo Html::panel([
                                'heading' => Icon::show('tags') . ' Цены',
                                'postBody' => Html::listGroup($prices),
                                'headingTitle' => true,
                            ],
                            Html::TYPE_PRIMARY) ?>
                </div>
            </div>
        </div>
    <?php endif ?>
    <?php if ($model->contacts): ?>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 robots-nocontent">
                    <!--googleoff: all-->
                    <!--noindex-->
                        <?php
                            echo Html::panel([
                                    'heading' => Icon::show('pencil-square-o') . ' Контакты',
                                    'postBody' => Html::tag('div', nl2br(Html::encode($model->contacts)), ['class' => 'contacts']),
                                    'headingTitle' => true,
                                ],
                                Html::TYPE_PRIMARY) ?>
                    <!--/noindex-->
                    <!--googleon: all-->
                </div>
            </div>
        </div>
    <?php endif ?>

    <?= Html::button(Icon::show('cogs') . ' Мастер', [
            'class' => 'btn btn-warning',
            'style' => 'margin-left:15px;',
            'id' => 'master-btn',
            'onclick' => new JsExpression('
                $("#master-modal").modal("show");
                $(".modal-backdrop").css({opacity: 0});
            '),
        ]) 
    ?>

</div>

<div class="service-description">
    <div class="row">
        <div class="col-md-12">
            <?= Html::tag('h2', 'Описание') ?>
            <?= $model->description ?>
        </div>
    </div>
</div>

<?php Modal::begin([
    'id' => 'master-modal',
    'options' => ['tabindex' => false, 'class' => 'manufacturer-modal'],
]); ?>
    
    <img src="<?= $model->thumbUrlManufacturer ?>">
<?php Modal::end(); ?>

</div>

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
                <?php foreach ($categories as $cat): ?>
                    <?php if ($cat['model']->isPurchase()): ?>
                        <?php $productsQuery = $cat['model']->getAllProductsQuery()
                                ->andWhere('visibility != 0')
                                ->andWhere('published != 0'); 
                            $products = $productsQuery->all();
                            $date = PurchaseProduct::getClosestDate($products);
                        ?>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <?php if ($cat['model']->isPurchase()): ?>
                            <div class="purchase-date-hdr">
                                <h5 class="text-center" style="font-size: 20px;"><strong><?= $date ? 'Закупка ' . date('d.m.Yг.', strtotime($date)) : '' ?></strong></h5>
                            </div>
                        <?php endif; ?>
                        <?= Html::a(
                                Html::img($cat['thumbUrl']),
                                $cat['url'],
                                ['class' => 'thumbnail', 'target' => $cat['options']['target']]
                        ) ?>
                        <h5 class="text-center" style="font-size: 20px;"><strong><?= $cat['content'] ?></strong></h5>
                    </div>
                <?php endforeach; ?>
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
