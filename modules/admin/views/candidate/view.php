<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\CandidateGroup;

/* @var $this yii\web\View */
/* @var $model app\models\Candidate */

$this->title = 'Кандидат: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Кандидаты', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить кандидата?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Группа',
                'value' => CandidateGroup::getGroupNameById($model->group_id),
            ],
            'email',
            'fio',
            'birthdate',
            'phone',
            'comment',
            'block_mailing',
        ],
    ]) ?>

</div>
