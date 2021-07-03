<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Provider */

$this->title = 'Поставщик: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Поставщики', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provider-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этого поставщика?',
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
            'name',
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
            'field_of_activity',
            'offered_goods',
            'legal_address',
            'snils',
            'ogrn',
            'site',
            'description'
        ],
    ]) ?>

</div>
