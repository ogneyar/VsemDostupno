<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
// use yii\widgets\MaskedInput;
use himiklab\yii2\recaptcha\ReCaptcha;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\models\User;
use app\models\City;
use app\models\Partner;
use app\models\Category;
use app\modules\purchase\models\PurchaseProduct;
use kartik\editable\Editable;


$constants = require(__DIR__ . '/../../../../../config/constants.php');
$web = $constants['WEB'];

/* @var $this yii\web\View */
$this->title = 'Упрощённая регистрация';
$this->params['breadcrumbs'] = [$this->title];

$script = <<<JS
    $(function () {
        $('#policy').change(function() {
            if (this.checked) {
                $('#register-button').attr('disabled', false);
            } else {
                $('#register-button').attr('disabled', true);
            }
        });
    });
JS;
$this->registerJs($script, $this::POS_END);
?>

<?php
if ($get['tg']) {
    $user = User::findOne(['tg_id' => $get['tg'], 'disabled' => 0]);
    if ($user) header("Location: https://".$_SERVER['SERVER_NAME'].$web."/profile/login");    
}
?>

<?= Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-category']) ?>

<?php 
// if (isset($get['tg'])) {
//     echo $get['tg'];
// }
?>

<div id="inner-cat">
<?php $form = ActiveForm::begin([
    'id' => 'register-small-form',
    'method' => 'post',
    'action' => $web.'/profile/register-small?tg='.$get['tg']."&role=".$get['role'],
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
        'labelOptions' => ['class' => 'col-md-2 control-label'],
    ],
]); ?>

<?php
    if ($get['role'] && $get['role'] != "provider") {
        echo("<div class='row'>");
            echo("<div class='col-md-offset-2 col-md-6'>");
                echo("<p>Выберите удобный адрес обслуживания.</p>");
            echo("</div>");
        echo("</div>");

        $data = [];
        foreach (City::find()->each() as $city) {
            $partners = Partner::find()
                ->joinWith(['user'])
                ->where('{{%partner}}.city_id = :city_id AND {{%user}}.disabled = 0', [':city_id' => $city->id])
                ->all();
            if ($partners) {
                foreach ($partners as $partner) {
                    $data[$partner->name] = [$partner->id => $city->name];
                }
                // $data[$city->name] = ArrayHelper::map($partners, 'id', 'name');

            }
        }
        echo $form->field($model, 'partner')->widget(Select2::className(), [
            'data' => $data,
            'language' => substr(Yii::$app->language, 0, 2),
            'options' => [
                'placeholder' => 'Выберите адрес',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    }
?>

    <?php
    if ($get['role'] && $get['role'] == "provider") {
        echo $form->field($model, 'lastname');
    }
    ?>
    <?= $form->field($model, 'firstname') ?>

    <?= $form->field($model, 'patronymic') ?>

    <?= $form->field($model, 'phone') ?>

    <?php
    if ($get['role'] && $get['role'] == "provider") {
        echo $form->field($model, 'description')->textArea(['rows' => 3, 'style' => 'resize: none;']);
    }
    ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Введите пароль для доступа к ресурсу.</p>
        </div>
    </div>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'password_repeat')->passwordInput() ?>
    
    <?php 
    echo $form->field($model, 're_captcha')->widget(ReCaptcha::className());
    // echo $form->field($model, 're_captcha');
    ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <input type="checkbox" name="policy" id="policy">
            <label for="policy" class="policy-label">Я соглашаюсь с <a href="<?= Url::to(['/page/policy']); ?>" target="_blank">условиями обработки и использования</a> моих персональных данных</label>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-8">
            <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary pull-right', 'name' => 'register-button', 'id' => 'register-button', 'disabled' => true]) ?>
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
