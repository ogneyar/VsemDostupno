<?php
use yii\helpers\Url;
use yii\bootstrap\Nav;
use kartik\helpers\Html;
use kartik\icons\Icon;
use yii\bootstrap\ActiveForm;
use kartik\typeahead\Typeahead;
use yii\web\JsExpression;

echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        [
            'label' => Icon::show('shopping-cart') . ' Корзина ' . Html::badge($cart->information, ['class' => 'cart-information']),
            'url' => Url::to(['/cart']),
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
            'items' => [
                [
                    'label' => Icon::show('list-alt') . ' История закупок',
                    'url' => Url::to(['/purchase/history']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ],
                [
                    'label' => Icon::show('list-alt') . ' История заказов',
                    'url' => Url::to(['/profile/partner/order']),
                ],
                [
                    'label' => Icon::show('users') . ' Участники группы',
                    'url' => Url::to(['/profile/partner/member']),
                ],
                /*[
                    'label' => Icon::show('bars') . ' Заказы моих участников',
                    'url' => Url::to(['/profile/partner/member/order']),
                ],*/
                [
                    'label' => Icon::show('bars') . ' Принять заказ',
                    'url' => Url::to(['profile/partner/member/order-create']),
                ],
                [
                    'label' => Icon::show('bars') . ' Групповая закупка',
                    'url' => Url::to(['/profile/provider/order/index']),
                    'visible' => Yii::$app->hasModule('purchase'),
                ],
                [
                    'label' => Icon::show('bars') . ' Групповой заказ на склад',
                    'url' => Url::to(['/profile/partner/order/index']),
                ],
                [
                    'label' => Icon::show('briefcase') . ' Мои услуги',
                    'url' => Url::to(['/profile/service']),
                ],
                [
                    'label' => Icon::show('pencil-square-o') . ' Личные данные',
                    'url' => Url::to(['/profile/partner/personal']),
                ],
                [
                    'label' => Icon::show('envelope-o') . ' - Письма - ',
                    'url' => Url::to(['/profile/email']),
                ],
                [
                    'label' => Icon::show('comments') . 'Вопросы, предложения',
                    'url' => Url::to(['/mailing/message']),
                    'visible' => Yii::$app->hasModule('mailing'),
                ],
                [
                    'label'=> Icon::show('search') . 'Поиск',
                    'url' => '#',
                    'options' => ['data-toggle' => 'modal', 'data-target'=>'#myModal'],
                ],
                [
                    'label' => Icon::show('sign-out') . ' Выход',
                    'url' => Url::to(['/profile/logout']),
                    
                ],
            ],
        ],
    ],
    'encodeLabels' => false,
]);

