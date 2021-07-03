<?php

use kartik\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
// use yii\widgets\MaskedInput;
use himiklab\yii2\recaptcha\ReCaptcha;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use app\models\City;
use app\models\Partner;
use app\models\Category;
use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
$this->title = 'Регистрация участника';
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

<?= Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-category']) ?>

<div id="inner-cat">
<?php $form = ActiveForm::begin([
    'id' => 'register-form',
    // 'method' => 'get',
    // 'action' => '/',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-4\">{error}</div>",
        'labelOptions' => ['class' => 'col-md-2 control-label'],
    ],
]); ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Выберите партнера у которого будете забирать свои заказы.</p>
        </div>
    </div>

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
            'options' => [
                'placeholder' => 'Выберите партнера ...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
    ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Введите свою персональную и контактную информацию, на основе которой будут подготовлены
            следующие документы: анкета участника, заявление, договор оферты, положение об участии в
            программе "Стол заказов".</p>
            <p>Предоставление неверных данных влечёт за собой автоматическое исключение из числа участников.</p>
        </div>
    </div>

    <?= $form->field($model, 'lastname') ?>

    <?= $form->field($model, 'firstname') ?>

    <?= $form->field($model, 'patronymic') ?>

    <?= $form->field($model, 'birthdate')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd.mm.yyyy',
        ],
    ]) ?>

    <?= $form->field($model, 'citizen') ?>

    <?= $form->field($model, 'registration', [
        'inputOptions' => [
            'data-toggle' => 'tooltip',
            'title' => 'Пример адреса: 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1098',
        ],
    ]) ?>

    <?= $form->field($model, 'residence', [
        'inputOptions' => [
            'data-toggle' => 'tooltip',
            'title' => 'Адрес фактического пребывания заполняется в случае отличия от адреса регистрации. Этот адрес будет использоваться для отправки корреспонденции, например, 143983, г.о. Балашиха, мкр. Ольгино, ул. Граничная 18, кв. 1101',
        ],
    ]) ?>

    <?= $form->field($model, 'passport') ?>

    <?= $form->field($model, 'passport_date')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_COMPONENT_APPEND,
        'readonly' => true,
        'layout' => '{input}{picker}',
        'pluginOptions' => [
            'autoclose' => true,
            'format' => 'dd.mm.yyyy',
        ],
    ]) ?>

    <?= $form->field($model, 'passport_department') ?>

    <?= $form->field($model, 'itn') ?>

    <?= $form->field($model, 'skills')->textArea(['rows' => '6']) ?>

    <?= $form->field($model, 'recommender_info', [
        'inputOptions' => [
            'data-toggle' => 'tooltip',
            'title' => 'Напишите ФИО или номер рекомендателя.',
        ],
    ]) ?>

    <?= $form->field($model, 'email') ?>

    <?= $form->field($model, 'phone') ?>

    <!-- <?/*= $form->field($model, 'phone')->widget(
        MaskedInput::className(), [
        'mask' => '+7 (999)-999-9999',
    ]) */?> -->

    <?= $form->field($model, 'ext_phones') ?>

    <div class="row">
        <div class="col-md-offset-2 col-md-6">
            <p>Введите пароль для доступа к ресурсу.</p>
        </div>
    </div>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'password_repeat')->passwordInput() ?>
    
    <?= $form->field($model, 're_captcha')->widget(ReCaptcha::className()) ?>

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
