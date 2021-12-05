<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\dropdown\DropdownX;
use yii\bootstrap\Modal;

$this->title = 'Распределить Фонды между контрагентами';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="fund-distribute">
    <h1><?= Html::encode($this->title) ?></h1>
<?php
$script = <<<JS
    $(function () {})
JS;
$this->registerJs($script, $this::POS_END);
?>
   
<br /><br />
<label>
    <input type="radio" value="Между Участниками" name="radio" /> 
    "Между Участниками" - 
    При проставлении Галочки в окне "Между Участниками", указанный процент из 
    этого фонда от каждой покупки будет зачислен на "Инвестиционный счёт" каждому контрагенту, по принципу КЭШ БЭК-а.
</label>
<br /><br />
<label>
    <input type="radio" value="Между партнёрами" name="radio" /> 
    "Между партнёрами" - 
    При проставлении Галочки в окне "Между партнёрами" указанный  процент от всех сделок будет зачисляться на ”Партнёрский счёт” - руководителя.
</label>
<br /><br />
<label>
    <input type="radio" value="На счёт ПО" name="radio" /> 
    "На счёт ПО" - 
    При проставлении Галочки в окне "На счёт ПО" указанный процент надбавки
    перечисляется на счёт ПО
</label>
<br /><br />
<label>
    <input type="radio" value="Фонд содружества" name="radio" /> 
    "Фонд содружества" - 
    При проставлении галочки в окне "Фонд содружества" указанный  процент из
    этого фонда от каждой покупки всех контрагентов зачисляется  в "Фонд содружества". 
</label>
<br /><br />
<label>
    <input type="radio" value="Рекомендательский счёт" name="radio" /> 
    "Рекомендательский сбор" - 
    При проставлении галочки в окне «Рекомендательский сбор» указанный  процент из
    этого фонда от каждой покупки зачисляется на рекомендательский счёт контрагента.
</label>
<br /><br />