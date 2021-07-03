<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Панель управления';
$this->params['breadcrumbs'][] = $this->title;

$updateStateUrl = Url::to(['/admin/module/update-state']);
$script = <<<JS
$(function () {
    $('input[type="checkbox"][class="update-state"]').on('change', function () {
        $.ajax({
            url: '$updateStateUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-module-id'),
                state: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    alert('Ошибка обновления состояния модуля');
                }
                window.location.reload();
            },
            error: function () {
                alert('Ошибка обновления состояния модуля');
            },
        });
        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="module-index">
    <h1>Модули</h1>
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'description',
            [
                'attribute' => 'state',
                'content' => function ($model) {
                    return '<input type="checkbox" ' . ($model->state ? 'checked' : '') . ' data-module-id="' . $model->id . '" class="update-state">';
                }
            ],
        ],
    ]); ?>
</div>
<hr>
<div class="notice-index">
    <h1>Уведомления</h1>
    <h4>Электронные адреса для получения административных уведомлений</h4>
    <p>валидные e-mail, разделитель запятая(,)</p>
    <?php $form = ActiveForm::begin(); ?>
        <div class="form-group">
            <?= Html::textarea('notice_email', $emails, ['class' => 'form-control', 'style' => 'resize: none;']); ?>
        </div>
        
        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>