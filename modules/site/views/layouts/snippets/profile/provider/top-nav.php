<?php
use yii\helpers\Url;
use yii\bootstrap\Nav;
use kartik\helpers\Html;
use kartik\icons\Icon;
use app\models\Member;
use app\models\User;

// $cart_vis = false;
$cart_vis = true;
if (Yii::$app->user->identity->role == User::ROLE_PROVIDER) {
    $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
    if ($member) {
        $cart_vis = true;
    }
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        [
            'label' => 'Контакты',
            'url' => Url::to(['/page/kontakty']),
        ],
        [
            'label' => 'О нас',
            'url' => Url::to(['/page/o-nas']),
        ],
        [
            'label' => Icon::show('shopping-cart') . ' Корзина ' . Html::badge($cart->information, ['class' => 'cart-information']),
            'url' => Url::to(['/cart']),
            'visible' => $cart_vis
        ],
        [
            'label' => Icon::show('credit-card') . ' Счет ' . Html::badge(Yii::$app->user->identity->entity->deposit->total),
            'url' => Url::to(['/profile/account']),
        ],
        [
            'label' => Icon::show('info-circle') . ' Информация',
            'items' => [
                [
                    'label' => Icon::show('list') . ' Прайс-лист: Заказы на склад',
                    'url' => Url::to(['/pricelist/product']),
                ],
                [
                    'label' => Icon::show('list') . ' Прайс-лист: Коллективные закупки',
                    'url' => Url::to(['/purchase/pricelist']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ],
                [
                    'label' => Icon::show('rouble') . ' Оплата',
                    'url' => Url::to(['/page/oplata']),
                ],
                [
                    'label' => Icon::show('gift') . ' Доставка',
                    'url' => Url::to(['/page/dostavka']),
                ],
            ],
        ],
        [
            'label' => Icon::show('user') . ' ' . Html::encode(Yii::$app->user->identity->entity->shortName),
            'url' => Url::to(['/profile']),
            'options' => ['id' => 'user-menu-lnk'],
            'items' => [
                /*[
                    'label' => Icon::show('list-alt') . ' Мои товары',
                    'url' => Url::to(['/profile/provider/product']),
                ],*/
                [
                    'label' => Icon::show('list-alt') . ' История закупок',
                    'url' => Url::to(['/purchase/history']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ],
                [
                    'label' => Icon::show('list-alt') . ' История заказов',
                    'url' => Url::to(['/profile/member/order']),
                    'visible' => $cart_vis
                ],
                [
                    'label' => Icon::show('bars') . ' Быстрый заказ',
                    'url' => Url::to(['profile/account/order-create']),
                ],
                [
                    'label' => Icon::show('briefcase') . ' Мои услуги',
                    'url' => Url::to(['/profile/service']),
                ],
                [
                    'label' => Icon::show('list-alt') . ' Внесён пай товаром',
                    'url' => Url::to(['/site/stock']),
                ],
                [
                    'label' => Icon::show('list-alt') . ' Поступившие заказы',
                    'url' => Url::to(['/purchase/provider']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ],
                [
                    'label' => Icon::show('pencil-square-o') . ' Личные данные',
                    'url' => Url::to(['/profile/provider/personal']),
                ],
                [
                    'label' => Icon::show('envelope-o') . ' - Письма - ',
                    'url' => Url::to(['/profile/email']),
                ],
                [
                    'label' => Icon::show('envelope-o') . 'Настройка рассылки',
                    'url' => 'javascript:void(0)',
                    'visible' => Yii::$app->hasModule('mailing'),
                    'options' => ['data-toggle' => 'modal', 'data-target' => '#mailing-settings'],
                ],
                [
                    'label' => Icon::show('check-square-o') . 'Проголосуйте',
                    'url' => Url::to(['/mailing/vote']),
                    'visible' => Yii::$app->hasModule('mailing'),
                    'options' => ['id' => 'vote-menu-lnk'],
                ],
                [
                    'label' => Icon::show('comments') . 'Вопросы, предложения',
                    'url' => Url::to(['/mailing/message']),
                    'visible' => Yii::$app->hasModule('mailing'),
                ],
                /*[
                    'label'=>'Учёт товаров/остатки',
                    'url'=>Url::to(['/site/stock']),
                ],*/
                [
                    'label' => Icon::show('sign-out') . ' Выход',
                    'url' => Url::to(['/profile/logout']),
                ],
            ],
        ],
    ],
    'encodeLabels' => false,
]);
