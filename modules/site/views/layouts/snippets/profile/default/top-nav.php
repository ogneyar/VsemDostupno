<?php
use yii\helpers\Url;
use yii\bootstrap\Nav;
use kartik\helpers\Html;
use kartik\icons\Icon;

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        [
            'label' => Icon::show('shopping-cart') . ' Корзина ' . Html::badge($cart->information, ['class' => 'cart-information']),
            'url' => Url::to(['/cart']),
        ],
        [
            'label' => Icon::show('list') . ' Прайс-лист',
            'items' => [
                [
                    'label' => 'Заказы на склад',
                    'url' => Url::to(['/pricelist/product']),
                ],
                [
                    'label' => 'Коллективные закупки',
                    'url' => Url::to(['/purchase/pricelist']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ]
            ],
            
        ],
        [
            'label' => Icon::show('rouble') . ' Оплата',
            'url' => Url::to(['/page/oplata']),
        ],
        [
            'label' => Icon::show('gift') . ' Доставка',
            'url' => Url::to(['/page/dostavka']),
        ],
        [
            'label' => Icon::show('user') . ' Кабинет',
            'url' => Url::to(['/profile']),
            'items' => [
                [
                    'label' => Icon::show('sign-in') . ' Вход',
                    'url' => Url::to(['/profile/login']),
                ],
                [
                    'label' => Icon::show('user-plus') . ' Регистрация участника',
                    'url' => Url::to(['/profile/register']),
                ],
                [
                    'label'=> Icon::show('user-plus') . 'Регистрация поставщика',
                    'url'=>Url::to(['/profile/register-provider']),
                ],
            ],
        ],
    ],
    'encodeLabels' => false,
]);
