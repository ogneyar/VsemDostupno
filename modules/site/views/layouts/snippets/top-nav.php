<?php
use yii\helpers\Url;
use yii\bootstrap\NavBar;
use kartik\helpers\Html;
use app\models\User;

NavBar::begin([
    'brandLabel' => Html::tag('div', Html::encode(Yii::$app->params['name']), ['class' => 'pull-left']),
    'brandUrl' => Url::to(['/']),
    'options' => [
        'class' => 'navbar-default navbar-fixed-top',
    ],
]);

$profile = Yii::$app->user->isGuest ? 'default' : (Yii::$app->user->identity->role == User::ROLE_SUPERADMIN ? User::ROLE_ADMIN : Yii::$app->user->identity->role);
echo $this->renderFile('@app/modules/site/views/layouts/snippets/profile/' . $profile . '/top-nav.php', [
    'cart' => $cart,
]);

NavBar::end();
