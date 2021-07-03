<?php

use yii\web\View;
use yii\bootstrap\Alert;
use yii\web\JsExpression;
use yii\helpers\Url;
use kartik\helpers\Html;
use kartik\icons\Icon;
use dosamigos\selectize\SelectizeDropDownList;
use app\models\Parameter;
use app\models\Cart;
use app\models\Category;

use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = 'Корзина';
$this->params['breadcrumbs'][] = $this->title;

?>

<?= Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-category']) ?>

<div class="cart" id="inner-cat">
    <?php if ($model->isEmpty()): ?>
        <div class="row">
            <div class="col-md-12">
                <?= Alert::widget([
                    'body' => 'Пока пусто.',
                    'options' => [
                        'class' => 'alert-info',
                    ],
                ])?>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Товары</th>
                            <th class="col-md-2 text-center">Количество</th>
                            <th class="col-md-2 text-center">Цена</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->products as $product): ?>
                            <tr>
                                <td class="row">
                                    <div class="col-md-2">
                                        <?= Html::a(
                                            Html::img($product->product->thumbUrl),
                                            $product->product->url,
                                            ['class' => 'thumbnail']
                                        ) ?>
                                    </div>
                                    <div class="col-md-10">
                                        <p>
                                            <?= Html::a(Html::encode($product->product->name), $product->product->url) ?>
                                        </p>
                                        <p>
                                            <?= $product->tare . ', ' . $product->volume . ' ' . $product->measurement; ?>
                                            <?= Html::badge(Html::encode($product->formattedCalculatedPrice)) ?>
                                        </p>
                                        <?php if ($product->product->isPurchase()): ?>
                                            
                                             
                                            <?php $purchase = PurchaseProduct::getPurchaseDateByFeature($product->id);?>
                                            <p>
                                                <b>Закупка:</b>
                                                <?= Html::a(
                                                    $purchase[0]->htmlFormattedPurchaseDate,
                                                    $product->product->category->url
                                                ) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php $f_quantity = $product->product->isPurchase() ? 100 : ($product->is_weights == 1 ? $product->quantity / $product->volume : $product->quantity) ?>
                                    <?= SelectizeDropDownList::widget([
                                        'name' => 'quantity',
                                        'value' => $product->cart_quantity,
                                        'items' => range(0, $f_quantity),
                                        'options' => [
                                            'data-product-id' => $product->id,
                                            'readonly' => true,
                                            'onchange' => new JsExpression('
                                                var id = $(this).attr("data-product-id");
                                                var quantity = $(this).val();

                                                $(this).prop("disabled", true);
                                                WidgetHelpers.showLoading();

                                                if (CartHelpers.update(id, quantity)) {
                                                    WidgetHelpers.hideLoading();
                                                    $(".cart-information").text(CartHelpers.Information);
                                                    $("td[data-product-id=\"" + id + "\"]").text(CartHelpers.UpdatedProductInformation);
                                                    $(this)[0].selectize.setValue(CartHelpers.UpdatedProductQuantity, true);
                                                    $(".cart button.order").prop("disabled", !CartHelpers.Order);
                                                    $(".cart button.clear").prop("disabled", !CartHelpers.Order);
                                                } else {
                                                    WidgetHelpers.hideLoading();
                                                    WidgetHelpers.showFlashDialog(CartHelpers.Message);
                                                }

                                                $(this).prop("disabled", false);

                                                return false;
                                            '),
                                        ],
                                    ]) ?>
                                </td>
                                <td class="text-center" data-product-id="<?= $product->id ?>"><?= Html::encode($product->formattedCalculatedTotalPrice) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row text-right total">
            <div class="col-md-12">
                <?= Html::tag('b', 'Итого: ' . Html::tag('span', Html::encode($model->formattedTotal), ['class' => 'cart-information'])) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <?= Html::button(Icon::show('trash') . ' Очистить', [
                    'class' => 'btn btn-danger clear',
                    'onclick' => new JsExpression('
                        $(this).prop("disabled", true);

                        yii.confirm("Очистить содержимое корзины?", function () {
                            WidgetHelpers.showLoading();
                            if (CartHelpers.clear()) {
                                location.reload();
                            } else {
                                WidgetHelpers.hideLoading();
                                WidgetHelpers.showFlashDialog(CartHelpers.Message);
                            }
                        });

                        $(this).prop("disabled", false);

                        return false;
                    '),
                ]) ?>
            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4 text-right">
                <?= Html::button(Icon::show('check') . ' Оформить заказ', [
                    'class' => 'btn btn-success order',
                    'onclick' => new JsExpression('
                        $(this).prop("disabled", true);
                        window.location.href = "' . Url::to(['/cart/order']) . '";

                        return false;
                    '),
                ]) ?>
            </div>
        </div>
    <?php endif ?>
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

