<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use kartik\tabs\TabsX;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
// use yii\widgets\MaskedInput;
use yii\widgets\Pjax;
use kartik\date\DatePicker;
use app\models\Candidate;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Кандидаты';
$this->params['breadcrumbs'][] = $this->title;

$updateBlockingUrl = Url::to(['/admin/candidate/update-blocking']);
$script = <<<JS
$(function () {
    $('input[type="checkbox"][class="update-block-mailing"]').on('change', function () {
        $.ajax({
            url: '$updateBlockingUrl',
            type: 'POST',
            data: {
                id: $(this).attr('data-candidate-id'),
                block: $(this).is(':checked') ? 1 : 0
            },
            success: function (data) {
                if (!(data && data.success)) {
                    alert('Ошибка обновления блокировки');
                }
            },
            error: function () {
                alert('Ошибка обновления блокировки');
            },
        });

        return false;
    });
})
JS;
$this->registerJs($script, $this::POS_END);

$dd_items = $items = [];
if (count($groups)) {
    $dd_items = ArrayHelper::map($groups, 'id', 'name');
    foreach ($groups as $key => $val) {
        $idx = "dp-tab";
        $active = false;
        
        if (isset($_GET[$idx]) && $_GET[$idx] == $key) {
            $active = true;
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => Candidate::find()->where(['group_id' => $val['id']]),
            'pagination' => [
                'params' => array_merge($_GET, ['dp-tab' => $key]),
                'route' => '/admin/candidate/index',
            ],
        ]);
        
        $items[] = [
            'active' => $active,
            'label' => $val['name'],
            'content' => 
            Html::a('Изменить группу', ['/admin/candidate-group/update', 'id' => $val->id], ['class' => 'btn btn-success update-group-btn', 'data-toggle' => 'modal', 'data-target' => '#update-group-modal', 'data-id' => $val->id, 'data-name' => $val->name]) .
            '&nbsp;&nbsp;&nbsp;' .
            Html::a('Удалить группу', ['/admin/candidate-group/delete', 'id' => $val->id], ['class' => 'btn btn-danger', 'data-pjax' => 0, 'data-method' => 'post', 'data-confirm' => "Вы уверены что хотите удалить группу и всех кандидатов в ней?"]) .
            '<br><br>' .
            GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'email',
                    'fio',
                    'comment',
                    [
                        'attribute' => 'block_mailing',
                        'content' => function ($model) {
                            return '<input type="checkbox" ' . ($model->block_mailing ? 'checked' : '') . ' data-candidate-id="' . $model->id . '" class="update-block-mailing">';
                        }
                    ],

                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ])
        ];
    }
    //print_r($items);
}

?>
<div class="candidate-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить кандидата', ['create'], [
            'id' => 'add-candidate-btn',
            'class' => 'btn btn-success',
            'data-toggle' => 'modal',
            'data-target' => '#add-candidate-modal',
        ]); ?>
        <?= Html::a('Добавить группу', ['/admin/candidate-group/create'], ['class' => 'btn btn-success', 'data-toggle' => 'modal', 'data-target' => '#add-group-modal']) ?>
    </p>
    <br />
    
    <?php if (count($items)): ?>
        <?= TabsX::widget([
            'items' => $items,
            'position' => TabsX::POS_LEFT,
            'encodeLabels' => false
        ]); ?>
    <?php endif; ?>
</div>

<?php Modal::begin([
    'id' => 'add-group-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Добавить группу кандидатов' . '</h4>',
]); ?>
    
    <?php $form = ActiveForm::begin(['action' => ['/admin/candidate-group/create']]); ?>
    
    <?= $form->field($modelGroup, 'name')->textInput(['maxlength' => true]) ?>
    
    <div class="form-group" style="text-align: right;">
        <?= Html::button('Закрыть', ['class' => 'btn btn-default', 'data-dismiss' => 'modal', 'aria-hidden' => 'true']) ?>
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
<?php Modal::end(); ?>

<?php Modal::begin([
    'id' => 'update-group-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Изменить группу кандидатов' . '</h4>',
]); ?>
    
    <?php $form = ActiveForm::begin(['action' => ['/admin/candidate-group/update'], 'id' => 'update-group-frm']); ?>
    
    <?= $form->field($modelGroup, 'name')->textInput(['maxlength' => true, 'id' => 'update-group-name-txt']) ?>
    
    <div class="form-group" style="text-align: right;">
        <?= Html::button('Закрыть', ['class' => 'btn btn-default', 'data-dismiss' => 'modal', 'aria-hidden' => 'true']) ?>
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
<?php Modal::end(); ?>

<?php Modal::begin([
    'id' => 'add-candidate-modal',
    'options' => ['tabindex' => false,],
    'header' => '<h4>' . 'Добавить кандидата' . '</h4>',
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false
    ],
]); ?>

<?php Modal::end(); ?>