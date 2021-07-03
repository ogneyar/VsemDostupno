<?php

use yii\helpers\Html;

$this->title = "Изменение заявки";
$this->params['breadcrumbs'][] = ['label' => 'Заявки на вступление', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => isset($model->partner) ? 'Заявка на вступление в качестве Участника' : 'Заявка на вступление в качестве Поставщика', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (isset($model->partner)): ?>
        <?= $this->render('_form_member', [
            'model' => $model,
        ]) ?>
    <?php else: ?>
        <?= $this->render('_form_provider', [
            'model' => $model,
        ]) ?>
    <?php endif; ?>
</div>
