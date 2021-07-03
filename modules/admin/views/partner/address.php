<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Адрес для поставщика';
$this->params['breadcrumbs'][] = ['label' => 'Партнеры', 'url' => '/admin/partner'];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="member-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <h4><?= $model->name; ?></h4>
    
    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>
    
        <?= $form->field($model, 'address')->textArea(['rows' => '6']) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']); ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>