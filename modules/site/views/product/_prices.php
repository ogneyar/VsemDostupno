<?php
use kartik\helpers\Html;
use kartik\icons\Icon;

$prices = [
    [
        'content' => 'Стоимость для всех желающих',
        'badge' => $all_price,
        'options' => ['class' => !Yii::$app->user->isGuest ? 'disabled' : ''],
    ],
    [
        'content' => 'Стоимость для участников ПО',
        'badge' => $member_price,
        'options' => ['class' => Yii::$app->user->isGuest ? 'disabled' : ''],
    ],
];

echo Html::panel([
        'heading' => Icon::show('tags') . ' Стоимость',
        'postBody' => Html::listGroup($prices),
        'headingTitle' => true,
    ],
    Html::TYPE_PRIMARY)

?>