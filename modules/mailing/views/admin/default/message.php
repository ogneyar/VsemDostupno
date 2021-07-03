<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Вопросы, жалобы, предложения';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mailing-message">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'sent_date',
                'format' => ['date', 'php:d.m.Y']
            ],
            [
                'attribute' => 'user_id',
                'content' => function ($model) {
                    return $model->user->fullName;
                }
            ],
            [
                'attribute' => 'category',
                'content' => function ($model) {
                    return $model->categoryTextRaw;
                }
            ],
            [
                'attribute' => 'answered',
                'content' => function ($model) {
                    return $model->answered == 1 ? 'Отвечено' : 'Не отвечено';
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['message-view', 'id' => $model->id]));
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::to(['message-delete', 'id' => $model->id]));
                    },
                ],
            ],
        ],
    ]); ?>
</div>