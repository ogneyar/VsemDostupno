<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\modules\admin\widgets\NestedList;
use app\models\Category;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Категории';
$this->params['breadcrumbs'][] = $this->title;
$hasModule = Yii::$app->hasModule('purchase') ? "1" : "0";
$script = <<<JS
$(function () {
    $('#update-structure .nested').on('change', function () {
        $('#update-structure-btn').removeClass('hidden');
    });

    $('#update-structure-btn').on('click', function () {
        $('#update-structure-btn').prop('disabled', true);

        var data = JSON.stringify($('#update-structure .nested').nestable('serialize'));
        $('#update-structure input[name="data"]').val(data);

        $('#update-structure').submit();

        return false;
    });
    $.ajax({
        url: '/web/admin/category/get-checked',
        dataType: "json",
        success: function (data, textStatus) {
            $.each(data, function(i, val) {
                $('#for-reg-' + val).attr('checked', true);
            });
        }
    });
    $('[name = for_reg]').change(function() {
        var check = 0;
        if (this.checked) {
            check = 1;
        }
        var html = $.ajax({
            url: "/web/admin/category/change-for-reg",
            async: false,
            type: "POST",
            data: {id: $(this).val(), checked: check}
        }).responseText;
    });
    
    if ($hasModule == '0') {
        $('[data-id="24"]').hide();
    }
})
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить категорию', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Сохранить структуру категорий', ['#'], ['class' => 'btn btn-primary hidden', 'id' => 'update-structure-btn']) ?>
    </p>

    <?php $form = ActiveForm::begin([
        'id' => 'update-structure',
        'action' => Url::to(['/admin/category/update-structure']),
    ]); ?>

    <?= Html::hiddenInput('data', '') ?>

    <?= NestedList::widget([
        'items' => Category::find()->tree(),
        'actions' => true,
    ]) ?>

    <?php ActiveForm::end(); ?>
</div>
