<?php
use yii\helpers\Url;
use kartik\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\Category;
use app\modules\purchase\models\PurchaseProduct;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Вход в личный кабинет';
$this->params['breadcrumbs'][] = $this->title;
?>


<?= Html::pageHeader(Html::encode($this->title), '', ['id' => 'page-header-category']) ?>


<!-- 
<div style="text-align:center;">
    <div>
        <p>Если Вы регистрировались с помощью нашего телеграм-бота, то можете войти с помощью него.</p>
        <p>Для этого нажмите кнопку ниже. После этого Вам будет предложенно перейти в телеграм, а в телеграм Вам бот подскажет что делать далее.</p>
        <br/>            
    </div>
    <div>
        <a href="https://t.me/bud_zdorov_rus_bot?start=login"><button>Подключиться к боту</button></a>
        <br/>            
        <br/>            
        <br/>            
    </div>
    <div>
        <p>Если Вы регистрировались БЕЗ нашего телеграм-бота, то можете войти с помощью Вашего email, указанного при регистрации.</p>
        <br/>            
    </div>
</div> -->



<div id="inner-cat">
<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-md-3\">{input}</div>\n<div class=\"col-md-8\">{error}</div>",
        'labelOptions' => ['class' => 'col-md-2 control-label'], 
    ],
]); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group forgot-password">
        <div class="col-md-5 text-right">
            <?= Html::a('Забыли пароль?', Url::to(['/profile/forgot-request'])) ?>
        </div>
    </div>

    <?= $form->field($model, 'rememberMe')
        ->checkbox([
            'template' => "<div class=\"col-md-offset-2 col-md-3\">{input} {label}</div>\n<div class=\"col-md-8\">{error}</div>",
        ]) ?>

    <div class="form-group">
        <div class="col-md-5">
            <?= Html::submitButton('Войти', ['class' => 'btn btn-primary pull-right', 'name' => 'login-button']) ?>
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
