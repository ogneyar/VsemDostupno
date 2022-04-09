<?php

use yii\web\View;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
// use yii\widgets\MaskedInput;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\select2\Select2;
use dosamigos\selectize\SelectizeDropDownList;
use app\models\City;
use app\models\User;
use app\models\Partner;
use app\models\Category;

use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = 'Оформить заказ';
$this->params['breadcrumbs'][] = $this->title;

?>

<div id="inner-cat">
<?= Html::pageHeader(Html::encode($this->title)) ?>

<?php $form = ActiveForm::begin([
    'id' => 'order-form',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-md-4\">{input}</div>\n<div class=\"col-md-6\">{error}</div>",
        'labelOptions' => ['class' => 'col-md-2 control-label'],
    ],
]); ?>

    <?php if ($model->canFilled('partner')): ?>
        <?php
            $data = [];
            foreach (City::find()->each() as $city) {
                $partners = Partner::find()
                    ->joinWith(['user'])
                    ->where('{{%partner}}.city_id = :city_id AND {{%user}}.disabled = 0', [':city_id' => $city->id])
                    ->all();
                if ($partners) {
                    $data[$city->name] = ArrayHelper::map($partners, 'id', 'name');
                }
            }
            echo $form->field($model, 'partner')->widget(Select2::className(), [
                'data' => $data,
                'language' => substr(Yii::$app->language, 0, 2),
                'options' => ['placeholder' => 'Выберите партнера ...'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]);
        ?>
    <?php endif ?>

    <?php if ($model->canFilled('lastname')): ?>
        <?= $form->field($model, 'lastname') ?>
    <?php endif ?>

    <?php if ($model->canFilled('firstname')): ?>
        <?= $form->field($model, 'firstname') ?>
    <?php endif ?>

    <?php if ($model->canFilled('patronymic')): ?>
        <?= $form->field($model, 'patronymic') ?>
    <?php endif ?>

    <?php if ($model->canFilled('phone')): ?>
        <?= $form->field($model, 'phone')
        // ->widget(
        //     MaskedInput::className(), [
        //     'mask' => '+7 (999)-999-9999',
        // ]) 
        ?>
    <?php endif ?>

    <?php if ($model->canFilled('email')): ?>
        <?= $form->field($model, 'email') ?>
    <?php endif ?>

    <?php if ($model->canFilled('address')): ?>
        <?= $form->field($model, 'address')->textArea(['rows' => '6', 'placeholder' => 'Если нужна доставка, то заполните это поле.']) ?>
    <?php endif ?>

    <?php if ($model->canFilled('comment')): ?>
        <?= $form->field($model, 'comment')->textArea(['rows' => '6', 'placeholder' => 'Если хотите сообщить дополнительную информацию к заказу, то заполните это поле.']) ?>
    <?php endif ?>

    <div class="form-group">
        <div class="col-md-6">
            <?= Html::submitButton(Icon::show('send') . ' Отправить заказ', ['class' => 'btn btn-success pull-right', 'name' => 'send-button']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
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
