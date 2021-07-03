<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;

?>
<?php
    echo Nav::widget([
        'items' => [
            [
                'label' => 'Политика конфиденциальности',
                'url' => Url::to(['/page/policy']),
                'linkOptions' => ['style' => 'color: red;']
            ],
            [
                'label' => 'Пункты выдачи',
                'url' => Url::to(['/page/punkty-vydachi']),
            ],
            [
                'label' => 'Контакты',
                'url' => Url::to(['/page/kontakty']),
            ],
            [
                'label' => 'О нас',
                'url' => Url::to(['/page/o-nas']),
            ],
        ],
        'options' => ['class' => 'nav-pills pull-left'],
    ])
?>
