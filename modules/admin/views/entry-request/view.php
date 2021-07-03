<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Member */

$this->title = isset($model->partner_id) ? 'Заявка на вступление в качестве Участника' : 'Заявка на вступление в качестве Поставщика';
$this->params['breadcrumbs'][] = ['label' => 'Заявки на вступление', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Принять', ['accept', 'id' => $model->user->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Изменить', ['update', 'id' => $model->user->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->user->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить эту заявку?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php if (isset($model->partner_id)): ?>
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
    <?php else: ?>
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
    <?php endif; ?>

</div>
