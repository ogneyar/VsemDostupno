<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

use app\models\Member;
use app\models\Partner;
use app\models\Provider;

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

                    if ($model->user->role === "member") {

                        $member = Member::findOne(['user_id' => $model->user->id]);
                        return Html::a($model->user->fullName, Url::to(['/admin/member/view', 'id' => $member->id]));

                    }else if ($model->user->role === "partner") {

                        $partner = Partner::findOne(['user_id' => $model->user->id]);
                        return Html::a($model->user->fullName, Url::to(['/admin/partner/view', 'id' => $partner->id]));

                    }else if ($model->user->role === "provider") {

                        $provider = Provider::findOne(['user_id' => $model->user->id]);
                        return Html::a($model->user->fullName, Url::to(['/admin/provider/view', 'id' => $provider->id]));

                    }
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
                        // return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::to(['message-delete', 'id' => $model->id]));
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', 'javascript:void(0)', ['onclick' => 'messageConfirmDelete('.$model->id.')']);
                    },
                ],
            ],
        ],
    ]); ?>
</div>