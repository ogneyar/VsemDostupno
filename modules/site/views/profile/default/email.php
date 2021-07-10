<?php

use kartik\helpers\Html;
use app\models\EmailLetters;

/* @var $this yii\web\View */
$this->title = 'Письма';
$this->params['breadcrumbs'] = [$this->title];

$script = <<<JS
    $(function () {
        $("#button").on('click',() => {
            console.log("click");
            
        });
    });
JS;
$this->registerJs($script, $this::POS_END);
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>


<?php EmailLetters::getLetters($user_data); ?>

<!--<button id='button'> Нажми!!! </button>-->


<?php //EmailLetters::send(313, "тема3", "содеРжание3"); ?>

