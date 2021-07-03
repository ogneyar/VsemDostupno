<?php

use kartik\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
// use yii\widgets\MaskedInput;
use himiklab\yii2\recaptcha\ReCaptcha;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\models\City;
use app\models\Partner;
use wbraganca\fancytree\FancytreeWidget;
use app\models\Category;
use yii\web\JsExpression;
use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = 'Регистрация поставщика. Шаг ' . $step;
$this->params['breadcrumbs'] = [$this->title];

$script = <<<JS
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            placement: 'top',
            container: 'body'
        });
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

<div id="inner-cat">
<div class="steps-imgs">
    <?php 
        switch ($step) {
            case 1:
                echo '<img style="float: left;" src="/images/step_1_active.png">';
                echo '<img src="/images/step_2.png">';
                echo '<img style="float: right;" src="/images/step_3.png">';
            break;
            case 2:
                echo '<img style="float: left;" src="/images/step_completed.png">';
                echo '<img src="/images/step_2_active.png">';
                echo '<img style="float: right;" src="/images/step_3.png">';
            break;
            case 3:
                echo '<img style="float: left;" src="/images/step_completed.png">';
                echo '<img src="/images/step_completed.png">';
                echo '<img style="float: right;" src="/images/step_3_active.png">';
            break;
        }
    ?>
</div>
<br />
<?= Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-category']) ?>

<?php if ($step == 1): ?>
    <?php $form = ActiveForm::begin([
        'id' => 'register-provider-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
            'labelOptions' => ['class' => 'col-md-2 control-label'],
        ],
    ]); ?>

        <input name="reg_step" value="<?= $step; ?>" type="hidden">
        <?= $form->field($model, 'lastname') ?>
        
        <?= $form->field($model, 'firstname') ?>
        
        <?= $form->field($model, 'patronymic') ?>
        
        <?= $form->field($model, 'birthdate')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_COMPONENT_APPEND,
            'readonly' => true,
            'layout' => '{input}{picker}',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ]) ?>
        
        <?= $form->field($model, 'citizen') ?>

        <?= $form->field($model, 'registration', [
            'inputOptions' => [
                'data-toggle' => 'tooltip',
                'title' => 'Пример адреса: 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1098',
            ]
        ]) ?>
        
        <?= $form->field($model, 'passport') ?>

        <?= $form->field($model, 'passport_date')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_COMPONENT_APPEND,
            'readonly' => true,
            'layout' => '{input}{picker}',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
        ]) ?>

        <?= $form->field($model, 'passport_department') ?>
        
        <?= $form->field($model, 'phone') ?>

        <!-- <?/*= $form->field($model, 'phone')->widget(
            MaskedInput::className(), [
            'mask' => '+7 (999)-999-9999',
        ]) */?> -->
        
        <?= $form->field($model, 'ext_phones'); ?>

        <div class="form-group">
            <div class="col-md-8">
                <?= Html::submitButton('Дальше', ['class' => 'btn btn-primary pull-right', 'name' => 'register-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
<?php endif; ?>

<?php if ($step == 2): ?>
    <?php $form = ActiveForm::begin([
        'id' => 'register-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
            'labelOptions' => ['class' => 'col-md-2 control-label'],
        ],
    ]); ?>

        <input name="reg_step" value="<?= $step; ?>" type="hidden">
        <?= $form->field($model, 'name') ?>
        
        <?= $form->field($model, 'field_of_activity')->textArea(['rows' => 3, 'style' => 'resize: none;']); ?>
        
        <?= $form->field($model, 'itn') ?>

        <?= $form->field($model, 'snils') ?>

        <?= $form->field($model, 'ogrn') ?>
        
        <?= $form->field($model, 'legal_address', [
            'inputOptions' => [
                'data-toggle' => 'tooltip',
                'title' => 'Пример адреса: 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1098',
            ]
        ]) ?>
        
        <?= $form->field($model, 'site') ?>
        
        <?= $form->field($model, 'category')->hiddenInput()->label(false); ?>
        
        <div class="form-group field-providerregdata-categories">
            <label for="provider-categories" class="col-md-2 control-label">Укажите категории, к которым относятся Ваши товары</label>
            <?php
                $selected = empty($model->category) ? [] : json_decode($model->category);
                echo FancytreeWidget::widget([
                    'id' => 'w99',
                    'options' =>[
                        'class' => 'col-md-6',
                        'source' => Category::getFancyTree($selected, [], true, true),
                        'checkbox' => true,
                        'extensions' => ['edit', 'glyph', 'wide'],
                        'selectMode' => 3,
                        'glyph' => [
                            'map' => [
                                'doc' => 'glyphicon glyphicon-book',
                                'docOpen' => 'glyphicon glyphicon-book',
                                'checkbox' => 'glyphicon glyphicon-unchecked',
                                'checkboxSelected' => 'glyphicon glyphicon-check',
                                'checkboxUnknown' => 'glyphicon glyphicon-share',
                                'dragHelper' => 'glyphicon glyphicon-play',
                                'dropMarker' => 'glyphicon glyphicon-arrow-right',
                                'error' => 'glyphicon glyphicon-warning-sign',
                                'expanderClosed' => 'glyphicon glyphicon-plus-sign',
                                'expanderLazy' => 'glyphicon glyphicon-plus-sign',
                                'expanderOpen' => 'glyphicon glyphicon-minus-sign',
                                'folder' => 'glyphicon glyphicon-list',
                                'folderOpen' => 'glyphicon glyphicon-list',
                                'loading' => 'glyphicon glyphicon-refresh',
                            ],
                        ],
                        'select' => new JsExpression('function (event, data) {
                            var keys = [];
                            $.map(data.tree.getSelectedNodes(), function (node) {
                                keys.push(node.key);
                            });
                            $("input[name=\"ProviderRegData[category]\"]").val(JSON.stringify(keys));
                        }'),
                        'init' => new JsExpression('function (event, data) {
                            var init = $(this).fancytree("option", "select");
                            init(event, data);
                        }'),
                    ]
                ]);
            ?>
        </div>
        
        <div class="form-group">
            <div class="col-md-8">
                <?= Html::submitButton('Дальше', ['class' => 'btn btn-primary pull-right', 'name' => 'register-button']) ?>
            </div>
        </div>
        
    <?php ActiveForm::end(); ?>
<?php endif; ?>

<?php if ($step == 3): ?>
    <?php $form = ActiveForm::begin([
        'id' => 'register-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
            'labelOptions' => ['class' => 'col-md-2 control-label'],
        ],
    ]); ?>
    
        <input name="reg_step" value="<?= $step; ?>" type="hidden">
        <?= $form->field($model_user, 'email') ?>
        
        <div class="row">
            <div class="col-md-offset-2 col-md-6">
                <p>Введите пароль для доступа к ресурсу.</p>
            </div>
        </div>

        <?= $form->field($model_user, 'password')->passwordInput() ?>
        
        <?= $form->field($model_user, 'password_repeat')->passwordInput() ?>
        
        <?= $form->field($model_user, 're_captcha')->widget(ReCaptcha::className()) ?>
        
        <div class="row">
            <div class="col-md-offset-2 col-md-6">
                <input type="checkbox" name="policy" id="policy">
                <label for="policy" class="policy-label">Я соглашаюсь с <a href="<?= Url::to(['/page/policy']); ?>" target="_blank">условиями обработки и использования</a> моих персональных данных</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-8">
                <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary pull-right', 'name' => 'register-button', 'id' => 'register-button', 'disabled' => true]) ?>
            </div>
        </div>
    
    <?php ActiveForm::end(); ?>
<?php endif; ?>
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
