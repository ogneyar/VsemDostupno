<?php
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use nirvana\showloading\ShowLoadingAsset;
use raoul2000\widget\scrollup\Scrollup;
use raoul2000\bootswatch\BootswatchAsset;
use kartik\icons\Icon;
use app\assets\AppAsset;
use app\assets\BootboxAsset;
use app\models\Category;
use app\models\Product;
use app\models\Service;
use app\models\Cart;
use yii\bootstrap\ActiveForm;
use kartik\typeahead\Typeahead;
use yii\web\JsExpression;
use app\models\User;
use yii\bootstrap\Alert;

use app\modules\mailing\models\MailingCategory;
use app\modules\mailing\models\MailingUser;
use app\modules\mailing\models\MailingVote;


/* @var $this \yii\web\View */
/* @var $content string */

BootswatchAsset::$theme = Yii::$app->params['theme'];

AppAsset::register($this);
BootboxAsset::overrideSystemMessageBox();
ShowLoadingAsset::register($this);
Icon::map($this);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/favicon.png']);

function getMenuItems($andWhere = 'TRUE')
{
    $items = [];
    $categories = Category::find()
        ->roots()
        ->andWhere('visibility != 0')
        ->andWhere($andWhere)
        ->orderBy(['name' => SORT_ASC])
        ->all();
    foreach ($categories as $category) {
        $items[] = [
            'content' => Html::encode($category->fullName),
            'url' => $category->url,
        ];
    }

    return $items;
}

$menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
foreach ($menu_first_level as $menu_f_l) {
    if ($menu_f_l->name == "Товары") $newArray[] = $menu_f_l;    
}
foreach ($menu_first_level as $menu_f_l) {
    if ($menu_f_l->name != "Товары") $newArray[] = $menu_f_l;    
}
$menu_first_level = $newArray;

$recomendations = [];
$recomendations_root = Category::findOne('234');
if ($recomendations_root) {
    $recomendations = Category::getMenuItems($recomendations_root);
}

$recomendations = ArrayHelper::merge(
    [
        [
            'content' => 'Слушать радио',
            'url' => 'http://рага.рф',
            'options' => [
                'target' => '_blank',
            ],
        ],
    ],
    $recomendations
);

$catalogue = [];
$catalogue_root = Category::findOne('220');
if ($catalogue_root) {
    $catalogue = Category::getMenuItems($catalogue_root);
}

$purchases = [];
$purchase = Category::findOne(['slug' => Category::PURCHASE_SLUG]);
if ($purchase) {
    $purchases = Category::getMenuItems($purchase);
}


$account_routes = [
    'site/stock/index',
    'site/stock/contibute',
    'mailing/site/default/vote',
    'mailing/site/default/message',
    'site/profile/service/index',
    'site/profile/account/index',
    'site/profile/account/order-create',
    'site/profile/member/default/personal',
    'site/profile/member/default/order',
    'site/profile/member/default/email',
    'site/profile/provider/default/personal',
    'site/profile/provider/default/email',
    'site/profile/provider/order/index',
    'site/profile/provider/order/detail',
    'site/profile/provider/order/date',
    'site/profile/partner/default/personal',
    'site/profile/partner/default/order',
    'site/profile/partner/default/email',
    'site/profile/partner/member/index',
    'site/profile/partner/member/update',
    'site/profile/partner/member/order-create',
    'site/profile/partner/order/index',
    'site/search/search',
    'purchase/site/provider/index',
    'purchase/site/provider/contibute',
    'purchase/site/history/index',
    'purchase/site/history/details',
];

$menu_expanded = 0;
$exploded_path = explode('/', Yii::$app->request->pathInfo);
if (count($exploded_path) > 1) {
    if ($exploded_path[0] == 'category') {
        $category_model = Category::find()->where('id = :id OR slug = :slug', [':id' => $exploded_path[1], ':slug' => $exploded_path[1]])->one();
        $menu_expanded = $category_model->rootParent->id;
    } else if ($exploded_path[0] == 'product') {
        $product_model = Product::findOne($exploded_path[1]);
        $menu_expanded = $product_model->category->rootParent->id;
    } else if ($exploded_path[0] == 'service') {
        $service_model = Service::findOne($exploded_path[1]);
        $menu_expanded = $service_model->categories[0]->rootParent->id;
    }
}

$script = <<<JS
$(function () {
    $(".menu-panel").click(function(e) {
        var el = e.target;
        if ($(el).hasClass('list-group-item')) {
            return true;
        } else {
            var obj = this;
            $(".list-group").slideUp();
            if ($("#open-prev").val() == $(obj).attr('data-cat')) {
                if ($("#main-cat-level-1").is(':hidden')) {
                    $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeOut('300', function() {
                        $("#main-cat-level-1").fadeIn();
                    });
                } else {
                    $(obj).find(".list-group").slideDown();
                    $("#main-cat-level-1").fadeOut('300', function() {
                        $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeIn();
                    });
                }
            } else {
                if ($("#open-prev").val() == "") {
                    if ($("#main-cat-level-1").is(':visible')) {
                        $("#main-cat-level-1").fadeOut('300', function() {
                            $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeIn();
                        });
                    }
                    
                    if ($(obj).attr('data-cat') == $menu_expanded) {
                        $("#inner-cat, #inner-product, #inner-service, #inner-alert-info, #inner-cate-descr, #page-header-category").fadeOut('10', function() {
                            $("#main-cat-level-1").fadeIn();
                        });
                    } else {
                        $(obj).find(".list-group").slideDown();
                        $("#inner-cat, #inner-product, #inner-service, #inner-alert-info, #inner-cate-descr, #page-header-category").fadeOut('10', function() {
                            $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeIn();
                        });
                    }
                } else {
                    if ($(obj).find(".list-group").is(':hidden')) {
                        $(obj).find(".list-group").slideDown();
                        if ($("#main-cat-level-1").is(':hidden')) {
                            $("#main-cat-level-2-" + $("#open-prev").val()).fadeOut('300', function() {
                                $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeIn();
                            });
                        } else {
                            $("#main-cat-level-1").fadeOut('300', function() {
                                $("#main-cat-level-2-" + $(obj).attr('data-cat')).fadeIn();
                            });
                        }
                    }
                }
            }
            $("#open-prev").val($(obj).attr('data-cat'));
        }
    });
})
JS;
$this->registerJs($script, $this::POS_END);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php Scrollup::widget([
            'theme' => Scrollup::THEME_PILLS,
            'pluginOptions' => [
                'scrollText' => 'Наверх',
                'scrollName'=> 'scrollUp',
                'topDistance'=> 400,
                'topSpeed'=> 3000,
                'animation' => Scrollup::ANIMATION_SLIDE,
                'animationInSpeed' => 200,
                'animationOutSpeed'=> 200,
                'activeOverlay' => false,
            ]
        ]) ?>
        <?php $this->beginBody() ?>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Поиск</h4>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'action' => Yii::$app->urlManager->createUrl(['site/search/search'])
                ]); ?>
                
                <label for="fio">Поиск по фамилии</label>
                <?php echo Typeahead::widget([
                    'name' => 'fio',
                    'options' => ['placeholder' => 'Начните вводить фамилию'],
                    'pluginOptions' => ['highlight'=>true],
                    'dataset' => [
                        [
                            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                            'display' => 'value',
                            'remote' => [
                                'url' => Url::to(['search/searchajax']) . '?name=%QUERY',
                                'wildcard' => '%QUERY'
                            ],
                        ]
                    ]
                ]); ?>
                
                <label for="reg_nom" style="margin-top: 20px;">Поиск по регистрационному номеру</label>
                <?php echo Typeahead::widget([
                    'name' => 'reg_Nom',
                    'options' => ['placeholder' => 'Начните вводить регистрационный номер'],
                    'pluginOptions' => ['highlight'=>true],
                    'dataset' => [
                        [
                            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                            'display' => 'value',
                            'remote' => [
                                'url' => Url::to(['search/searchajax']) . '?disc_number=%QUERY',
                                'wildcard' => '%QUERY'
                            ],
                        ]
                    ]
                ]); ?>

                <?php if (Yii::$app->hasModule('purchase')): ?>
                    <label for="purchase_order_number" style="margin-top: 20px;">Поиск по № предварительного заказа</label>
                    <?php echo Typeahead::widget([
                        'name' => 'purchase_order_number',
                        'options' => ['placeholder' => 'Начните вводить номер заказа'],
                        'pluginOptions' => ['highlight'=>true],
                        'dataset' => [
                            [
                                'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                                'display' => 'value',
                                'remote' => [
                                    'url' => Url::to(['search/searchajax']) . '?purchase_order_number=%QUERY',
                                    'wildcard' => '%QUERY'
                                ],
                            ]
                        ]
                    ]); ?>
                <?php endif; ?>
                
                <label for="nomer_order" style="margin-top: 20px;">Поиск по № заказа</label>
                <?php echo Typeahead::widget([
                    'name' => 'nomer_order',
                    'options' => ['placeholder' => 'Начните вводить номер заказа'],
                    'pluginOptions' => ['highlight'=>true],
                    'dataset' => [
                        [
                            'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                            'display' => 'value',
                            'remote' => [
                                'url' => Url::to(['search/searchajax']) . '?order_numb=%QUERY',
                                'wildcard' => '%QUERY'
                            ],
                        ]
                    ]
                ]); ?> 
                <input type="hidden" name='id' value="<?= Yii::$app->user->id ?>">
                <button type="submit" class="btn btn-success" style="width:150px; margin-left: 73%; margin-top: 5%;">Поиск</button>
                
                <?php $form= ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
        <div class="modal fade" id="providerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Стать поставщиком</h4>
                    </div>



                    <div class="modal-body">

                        <b>Желаете стать поставщиком собственных товаров, тогда Вам необходимо уведомить об этом администрацию сайта</b>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Передумал</button>
                        <?= Html::a('Уведомить', URL::to(['profile/member/becomeprovider']),['class'=>'btn btn-primary']); ?>
                    </div>
                </div>

            </div>
        </div>
        
        <?php if (Yii::$app->hasModule('mailing')): ?>
            <?php $this->registerJsFile('/js/mailing/common.js',  ['position' => $this::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]); ?>
            <div class="modal fade" id="mailing-settings" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Настройте для себя информационные категории сообщений</h4>
                        </div>
                        <div class="modal-body">
                            <?php $form = ActiveForm::begin(['id' => 'update-mailing-frm']); ?>
                                <?php $cats = MailingCategory::find()->where(['<>', 'id', 5])->all(); ?>
                                <?php if ($cats): ?>
                                    <?php foreach ($cats as $cat): ?>
                                        <?php $signed = MailingUser::find()->where(['user_id' => Yii::$app->user->id, 'mailing_category_id' => $cat->id])->exists(); ?>
                                        <div class="form-group">
                                            <?= Html::checkbox(
                                                'm_category[' . $cat->id . ']', 
                                                $cat->id == 1 ? true : ($signed ? true : false), 
                                                ['id' => 'm-id-' . $cat->id, 'disabled' => $cat->id == 1 ? true : false]
                                            ); ?>
                                            <label for="m-id-<?= $cat->id; ?>"><?= $cat->name; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <input type="hidden" name="user_id" id="user-id" value="<?= Yii::$app->user->id; ?>">
                                <p>Наличие галочки в окне категорий свидетельствует о согласии получать информацию.</p>
                            <?php ActiveForm::end(); ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                            <?= Html::a('Сохранить', 'javascript:void(0)',['class'=>'btn btn-primary', 'id' => 'update-mailing-btn']); ?>
                        </div>
                    </div>

                </div>
            </div>
            <input type="hidden" id="vote-active" value="<?= MailingVote::existsActiveVote(Yii::$app->user->id); ?>">
        <?php endif; ?>
            <div class="wrap">
<!--                <div class="top-season-decor"></div>-->
                <?= $this->renderFile('@app/modules/site/views/layouts/snippets/top-nav.php', [
                    'cart' => new Cart(),
                ]) ?>
                <div class="container">
                    <div class="row site-page">
                        <?//= Yii::$app->controller->route ?>
                        <?php if (!in_array(Yii::$app->controller->route, $account_routes)): ?>
                            <?php if ($menu_first_level): ?>
                                <div class="col-md-2">
                                    <?php foreach ($menu_first_level as $menu_f_l) {
                                        $items = Category::getMenuItems($menu_f_l);
                                        $show = true;
                                        if ($menu_f_l->isPurchase()) {
                                            $heading = Icon::show('calendar') . ' Закупки';
                                            if (!Yii::$app->hasModule('purchase')) {
                                                $show = false;
                                            }
                                        } else if ($menu_f_l->isRecomended()) {
                                            $heading = Icon::show('thumbs-o-up') . ' Рекомендуем';
                                        } else if ($menu_f_l->isStock()) {
                                            $heading = Icon::show('list') . ' В наличии';
                                        } else {
                                            $heading = Icon::show('list') . ' ' . $menu_f_l->name;
                                        }
                                        if ($show) {
                                            echo $this->renderFile('@app/modules/site/views/layouts/snippets/menu-panel.php', [
                                                'heading' => $heading,
                                                'items' => $items,
                                                'class' => 'menu-purchases',
                                                'data' => $menu_f_l->id,
                                                'style' => $menu_expanded == $menu_f_l->id ? "display: block;" : "display: none;",
                                            ]);
                                        }
                                    } ?>
                                    <input type="hidden" id="open-prev" value="">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="<?= !in_array(Yii::$app->controller->route, $account_routes) ? 'col-md-10' : 'col-md-12' ?>">
                            <?php if (in_array(Yii::$app->controller->route, $account_routes)): ?>
                                <?= Html::a('Товары / Услуги', Url::to(['/']), ['class' => 'btn btn-primary']) ?>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $content ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!--Показ всплывающего окошка об успехе-->
        <?php if (Yii::$app->session->hasFlash('Успех')){
            echo Alert::widget([
                    'options'=>['class'=>'alert-info'],
                'body'=>Yii::$app->session->getFlash('Успех'),
            ]);
        } ?>


        <!--Показ всплывающего окошка об успехе-->

        
        <!--LiveInternet counter--><a href="https://www.liveinternet.ru/click"
        target="_blank"><img id="licntC6F2" width="88" height="120" style="border:0;padding:10px;" 
        title="LiveInternet: показано количество просмотров и посетителей"
        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7"
        alt=""/></a><script>(function(d,s){d.getElementById("licntC6F2").src=
        "https://counter.yadro.ru/hit?t29.6;r"+escape(d.referrer)+
        ((typeof(s)=="undefined")?"":";s"+s.width+"*"+s.height+"*"+
        (s.colorDepth?s.colorDepth:s.pixelDepth))+";u"+escape(d.URL)+
        ";h"+escape(d.title.substring(0,150))+";"+Math.random()})
        (document,screen)</script><!--/LiveInternet-->


            <footer class="footer">
                <div class="container">
                    <?= $this->renderFile('@app/modules/site/views/layouts/snippets/bottom-nav.php') ?>
                    <p class="pull-right">&copy; <?= Html::encode(Yii::$app->params['name']) ?> <?= date('Y') ?></p>
                </div>
            </footer>
            
        <?php $this->endBody() ?>
        <?= $this->renderFile('@app/modules/site/views/layouts/snippets/flash-message.php') ?>
    </body>
</html>
<?php $this->endPage() ?>
