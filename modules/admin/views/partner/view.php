<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Partner */

$this->title = 'Партнер: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Партнеры', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="partner-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этого партнера?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'user_id',
            'tg_id',
            'disabled',
            'number',
            'createdAt',
            'created_ip',
            'logged_in_at',
            'logged_in_ip',
            'name',
            'cityName',
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
            'skills',
        ],
    ]) ?>

</div>
