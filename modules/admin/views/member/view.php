<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Member */

$this->title = 'Участник: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Участники', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этого участника?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'user_id',
            'disabled',
            'number',
            'createdAt',
            'created_ip',
            'logged_in_at',
            'logged_in_ip',
            'partnerName',
            'email',
            'phone',
            'ext_phones',
            'fullName',
            'birthdate',
            'citizen',
            'registration',
            'residence',
            'passport',
            'passport_date',
            'passport_department',
            'itn',
            'recommender_info',
            'skills',
        ],
    ]) ?>

</div>
