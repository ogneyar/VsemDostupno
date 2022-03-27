<?php

use yii\web\View;
use yii\bootstrap\Alert;
use yii\bootstrap\Modal;
use yii\web\JsExpression;
use yii\helpers\Url;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\models\Category;
use app\models\Parameter;
use app\models\Cart;
use app\models\User;
use app\models\Member;
use dosamigos\gallery\Gallery;
use dosamigos\selectize\SelectizeDropDownList;
use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = $model->name;

$url = Yii::$app->request->referrer;
if (preg_match('/\/category\/\d+$/', $url)) {
    $categoryId = preg_replace('/^\D+/', '', $url);
    $category = Category::find()
        ->where('visibility != 0 AND id = :id', [':id' => $categoryId])
        ->one();
    if ($category) {
        $this->params['breadcrumbs'] = $category->getBreadcrumbs($model->name);
    }
} else {
    $this->params['breadcrumbs'] = [];
    $this->params['breadcrumbs'][] = $model->name;
    //$category = $model->purchaseCategory;
}

$productImages = [];
foreach ($model->productHasPhoto as $productHasPhoto) {
    $productImages[] = [
        'url' => $productHasPhoto->imageUrl,
        'src' => $model->thumbUrl,
        'options' => ['class' => 'hidden'],
    ];
}

$manufacturerImage = [[
    'url' => $model->thumbUrlManufacturer,
    'src' => $model->thumbUrlManufacturer,
    'options' => ['class' => 'hidden'],
]];

$enableCart = false;
if (Yii::$app->user->isGuest) {
    $enableCart = true;
} else {
    if (!in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN,  User::ROLE_SUPERADMIN])) { // User::ROLE_PROVIDER,
        $enableCart = true;
    }
    // if (Yii::$app->user->identity->role == User::ROLE_PROVIDER) {
    //     $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
    //     if($member) {
    //         $enableCart = true;
    //     }
    // }
}

$features = [];
foreach ($model->productFeatures as $feat) {
    if ($feat->quantity > 0 || $model->isPurchase()) {
        if ($model->isPurchase()) {
            foreach ($feat->purchaseProducts as $prod) {
                if (strtotime($prod->stop_date) >= strtotime(date('Y-m-d')) && $prod->status == 'advance') {
                    $features[$feat->id] = (!empty($feat->tare) ? $feat->tare . ', ' : "") . $feat->volume . ' ' . $feat->measurement;
                }
            }
        } else {
            $features[$feat->id] = (!empty($feat->tare) ? $feat->tare . ', ' : "") . $feat->volume . ' ' . $feat->measurement;
        }
    }
}
?>

<div id="inner-cat">
<?= Html::pageHeader(Html::encode($model->name)) ?>

<div class="row">
    <div class="col-md-6">
        <?= Gallery::widget(['id' => 'product-images', 'items' => $productImages]) ?>
        <?= Html::a(
                Html::img($model->thumbUrl),
                '#',
                [
                    'class' => 'thumbnail',
                    'onclick' => new JsExpression('
                        $("#product-images a").first().trigger("click");
                        return false;
                    '),
                ]
        ) ?>
    </div>
    <div class="col-md-6">
        <?php if ($enableCart): ?>
            <div class="row add-product-to-cart-panel">
                <div class="col-md-5">
                    <?= SelectizeDropDownList::widget([
                        'name' => 'feature',
                        'items' => $features,
                        'options' => [
                            'readonly' => true,
                            'onchange' => new JsExpression('
                                $(".qnt-container").each(function() {
                                    $(this).hide();
                                });
                                $("#quantity-container-"+$(this).val()).show();
                                var html = $.ajax({
                                    url: "/site/product/get-prices",
                                    async: false,
                                    type: "POST",
                                    data: {f_id: $(this).val()}
                                }).responseText;
                                if (html) {
                                    $("#prices-container").html(html);
                                }
                                $.ajax({
                                    url: "/site/product/in-cart",
                                    type: "POST",
                                    data: {f_id: $(this).val()},
                                    success: function(response) {
                                        if (response) {
                                            $("#cart-btn").addClass("btn-product-in-cart");
                                            $("#cart-btn").removeClass("btn-success");
                                            $("#cart-btn").addClass("btn-info");
                                            $("#cart-btn").html(\'' . Icon::show('shopping-cart') . ' Товар в корзине\');
                                        } else {
                                            $("#cart-btn").removeClass("btn-product-in-cart");
                                            $("#cart-btn").removeClass("btn-info");
                                            $("#cart-btn").addClass("btn-success");
                                            $("#cart-btn").html(\'' . Icon::show('cart-plus') . ' Добавить в корзину\');
                                        }
                                    }
                                });
                                var html = $.ajax({
                                    url: "/site/product/get-purchase-date",
                                    type: "POST",
                                    async: false,
                                    data: {f_id: $(this).val(), url: $("#category-url").val()}
                                }).responseText;
                                if (html) {
                                    $("#dates-container").html(html);
                                }
                            '),
                        ],
                    ]) ?>
                </div>
                <?php $cnt_show = 1; $purch_to_show = false; ?>
                <?php foreach ($model->productFeatures as $k => $feat): ?>
                    <?php if ($feat->quantity > 0 || $model->isPurchase()): ?>
                        <?php $f_quantity = $model->isPurchase() ? 100 : ($feat->is_weights == 1 ? $feat->quantity / $feat->volume : $feat->quantity) ?>
                        <?php if ($model->isPurchase()): ?>
                            <?php foreach ($feat->purchaseProducts as $prod): ?>
                                <?php if (strtotime($prod->stop_date) >= strtotime(date('Y-m-d')) && $prod->status == 'advance'): ?>
                                    <?php $purch_to_show = true; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($purch_to_show): ?>
                                <div class="col-md-3 qnt-container" data-feature-id="<?= $feat->id; ?>" id="quantity-container-<?= $feat->id; ?>" <?php if ($cnt_show != 1): ?>style="display: none;"<?php endif; ?>>
                                    <?= SelectizeDropDownList::widget([
                                        'name' => 'quantity',
                                        'value' => Cart::hasQuantity($feat),
                                        'items' => array_combine(
                                            range(1, $f_quantity),
                                            range(1, $f_quantity)
                                        ),
                                        'options' => [
                                            'data-product-id' => $feat->id,
                                            'id' => $feat->id,
                                            'readonly' => true,
                                            'onchange' => new JsExpression('
                                                if ($(".btn-product-in-cart").length) {
                                                    var id = $(this).attr("data-product-id");
                                                    var quantity = $(this).val();

                                                    $(this).prop("disabled", true);
                                                    WidgetHelpers.showLoading();

                                                    if (CartHelpers.update(id, quantity)) {
                                                        WidgetHelpers.hideLoading();
                                                        WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                                        $(this)[0].selectize.setValue(CartHelpers.UpdatedProductQuantity, true);
                                                    } else {
                                                        WidgetHelpers.hideLoading();
                                                        WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                                        $(this).removeClass("btn-product-in-cart");
                                                        $(this).removeClass("btn-info");
                                                        $(this).addClass("btn-success");
                                                        $(this).html(\'' . Icon::show('cart-plus') . ' Добавить в корзину\');
                                                    }

                                                    if (CartHelpers.Information) {
                                                        $(".cart-information").text(CartHelpers.Information);
                                                    }

                                                    $(this).prop("disabled", false);
                                                }

                                                return false;
                                            '),
                                        ],
                                    ]) ?>
                                </div>
                                <?php $cnt_show = 0; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="col-md-3 qnt-container" data-feature-id="<?= $feat->id; ?>" id="quantity-container-<?= $feat->id; ?>" <?php if ($cnt_show != 1): ?>style="display: none;"<?php endif; ?>>
                                <?= SelectizeDropDownList::widget([
                                    'name' => 'quantity',
                                    'value' => Cart::hasQuantity($feat),
                                    'items' => array_combine(
                                        range(1, $f_quantity),
                                        range(1, $f_quantity)
                                    ),
                                    'options' => [
                                        'data-product-id' => $feat->id,
                                        'id' => $feat->id,
                                        'readonly' => true,
                                        'onchange' => new JsExpression('
                                            if ($(".btn-product-in-cart").length) {
                                                var id = $(this).attr("data-product-id");
                                                var quantity = $(this).val();

                                                $(this).prop("disabled", true);
                                                WidgetHelpers.showLoading();

                                                if (CartHelpers.update(id, quantity)) {
                                                    WidgetHelpers.hideLoading();
                                                    WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                                    $(this)[0].selectize.setValue(CartHelpers.UpdatedProductQuantity, true);
                                                } else {
                                                    WidgetHelpers.hideLoading();
                                                    WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                                    $(this).removeClass("btn-product-in-cart");
                                                    $(this).removeClass("btn-info");
                                                    $(this).addClass("btn-success");
                                                    $(this).html(\'' . Icon::show('cart-plus') . ' Добавить в корзину\');
                                                }

                                                if (CartHelpers.Information) {
                                                    $(".cart-information").text(CartHelpers.Information);
                                                }

                                                $(this).prop("disabled", false);
                                            }

                                            return false;
                                        '),
                                    ],
                                ]) ?>
                            </div>
                            <?php $cnt_show = 0; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="col-md-6">
                    <?php
                        if (Cart::hasProduct($model)) {
                            $icon = 'shopping-cart';
                            $title = 'Товар в корзине';
                            $class = 'btn-product-in-cart btn-info';
                        } else {
                            $icon = 'cart-plus';
                            $title = 'Добавить в корзину';
                            $class = 'btn-success';
                        }
                        echo Html::button(Icon::show($icon) . ' ' . $title, [
                            'class' => 'btn ' . $class,
                            'id' => 'cart-btn',
                            'onclick' => new JsExpression('
                                var obj = $(".qnt-container:visible");
                                var feature = $(obj).attr("data-feature-id");
                                var quantity = $("#"+feature).val();

                                $(this).prop("disabled", true);
                                
                                if ($(this).hasClass("btn-info")) {
                                    window.location.href = "' . Url::to(['/cart']) . '";
                                    return false;
                                } else {
                                    WidgetHelpers.showLoading();
                                }
                                
                                
                                
                                if (CartHelpers.add(feature, quantity)) {
                                    WidgetHelpers.hideLoading();
                                    WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                    $(this).addClass("btn-product-in-cart");
                                    $(this).removeClass("btn-success");
                                    $(this).addClass("btn-info");
                                    $(this).html(\'' . Icon::show('shopping-cart') . ' Товар в корзине\');
                                } else {
                                    WidgetHelpers.hideLoading();
                                    WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                    
                                    // console.log("CartHelpers.NOTadd");
                                }

                                if (CartHelpers.Information) {
                                    $(".cart-information").text(CartHelpers.Information);
                                }

                                $(this).prop("disabled", false);

                                return false;
                            '),
                        ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-12" id="prices-container">
                <?php
                    $prices = [
                        [
                            'content' => 'Стоимость для всех желающих',
                            'badge' => $model->productFeatures[0]->is_weights == 1 ? Yii::$app->formatter->asCurrency($model->formattedPrice * $model->productFeatures[0]->volume, 'RUB') : $model->formattedPrice,
                            'options' => ['class' => !Yii::$app->user->isGuest ? 'disabled' : ''], 
                        ],
                        [
                            'content' => 'Стоимость для участников ПО',
                            'badge' => $model->productFeatures[0]->is_weights == 1 ? Yii::$app->formatter->asCurrency($model->formattedMemberPrice * $model->productFeatures[0]->volume, 'RUB') : $model->formattedMemberPrice,
                            'options' => ['class' => Yii::$app->user->isGuest ? 'disabled' : ''],
                        ],
                    ];

                    echo Html::panel([
                            'heading' => Icon::show('tags') . ' Стоимость',
                            'postBody' => Html::listGroup($prices),
                            'headingTitle' => true,
                        ],
                        Html::TYPE_PRIMARY) ?>
            </div>
        </div>
        <?php if ($model->isPurchase()): ?>
            <input type="hidden" id="category-url" value="<?= $model->category->url ?>">
            <div class="row">
                <div class="col-md-12" id="dates-container">
                    <?php
                        $purchase_date_0 = $stop_date_0 = 9999999999;
                        foreach ($model->productFeatures as $val) {
                            foreach ($val->purchaseProducts as $prod) {
                                if (strtotime($prod->stop_date) >= strtotime(date('Y-m-d')) && $prod->status == 'advance') {
                                    if ($prod->purchase_date < $purchase_date_0) {
                                        $purchase_date_0 = $prod->purchase_date;
                                        $purchase_date_1 = $prod->htmlFormattedPurchaseDate;
                                    }
                                    if ($prod->stop_date < $stop_date_0) {
                                        $stop_date_0 = $prod->stop_date;
                                        $stop_date_1 = $prod->htmlFormattedStopDate;
                                    }
                                }
                            }
                        }
                        echo Alert::widget([
                            'body' => sprintf(
                                'Доставка состоится %s, заказы принимаются до %s включительно.',
                                Html::a($purchase_date_1, Url::to([$model->category->url])),
                                Html::a($stop_date_1, Url::to([$model->category->url]))
                            ),
                            'options' => [
                                'class' => 'alert-info alert-def',
                            ],
                        ]);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= Alert::widget([
                        'body' => Parameter::getValueByName('purchase-info'),
                        'options' => [
                            'class' => 'alert-info alert-def',
                        ],
                    ])?>
                </div>
            </div>
        <?php endif ?>
        <?= Html::button(Icon::show('cogs') . ' Производитель', [
                'class' => 'btn btn-warning',
                'id' => 'manufacturer-btn',
                'onclick' => new JsExpression('
                    $("#manufacturer-modal").modal("show");
                    $(".modal-backdrop").css({opacity: 0});
                '),
            ]) 
        ?>
    </div>
    
</div>

<div class="product-description">
    <?php
        $attributes = [
            'composition',
            'packing',
            'manufacturer',
            'status',
        ];
    ?>
    <?php foreach ($attributes as $attribute): ?>
        <?php if ($model->$attribute): ?>
            <div class="row">
                <div class="col-md-12">
                    <?= Html::tag('b', $model->getAttributeLabel($attribute) . ':') ?> <?= Html::encode($model->$attribute) ?>
                </div>
            </div>
        <?php endif ?>
    <?php endforeach ?>

    <div class="row">
        <div class="col-md-12">
            <?= Html::tag('h2', 'Описание') ?>
            <?= $model->description ?>
        </div>
    </div>
</div>

<?php Modal::begin([
    'id' => 'manufacturer-modal',
    'options' => ['tabindex' => false, 'class' => 'manufacturer-modal'],
]); ?>
    
    <img src="<?= $model->thumbUrlManufacturer ?>">
<?php Modal::end(); ?> 
</div>

<div class="product-panel">
    <div id="main-cat-level-1" style="display: none;">
        <!-- <?//= Html::pageHeader('Исходная') ?> -->
        <?php foreach ($menu_first_level as $item): ?>
            <div class="col-md-4">
                <?= Html::a(
                        Html::img($item->thumbUrl),
                        $item->url,
                        ['class' => 'thumbnail']
                ) ?>
                <!-- <h5 class="text-center" style="font-size: 20px;"><strong><?//= $item->name ?></strong></h5> -->
            </div>
        <?php endforeach; ?>
    </div>

    <?php foreach ($menu_first_level as $f_level): ?>
        <div id="main-cat-level-2-<?= $f_level->id ?>" class="main-cat-level-2" style="display: none;">
            <?php 
                if ($f_level->fullName == "Скидки") {
                    echo Html::pageHeader(Html::encode("Скидки наших Партнёров"));
                }else {
                    echo Html::pageHeader(Html::encode($f_level->fullName)); 
                }
            ?>
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
