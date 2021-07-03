<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;
use app\assets\BootboxAsset;
use nirvana\showloading\ShowLoadingAsset;
use raoul2000\widget\scrollup\Scrollup;
use raoul2000\bootswatch\BootswatchAsset;
use kartik\icons\Icon;
use app\models\Cart;
use yii\bootstrap\ActiveForm;
use kartik\typeahead\Typeahead;
use yii\web\JsExpression;

/* @var $this \yii\web\View */
/* @var $content string */

BootswatchAsset::$theme = Yii::$app->params['theme'];

AppAsset::register($this);
BootboxAsset::overrideSystemMessageBox();
ShowLoadingAsset::register($this);
Icon::map($this);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/images/favicon.png']);

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
            <div class="wrap">
                <?= $this->renderFile('@app/modules/site/views/layouts/snippets/top-nav.php', [
                    'cart' => new Cart(),
                ]) ?>
                <div class="container">
                    <?= $content ?>
                </div>
            </div>

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
