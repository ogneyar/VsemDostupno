<?php

use kartik\helpers\Html;

$this->title = $title;
$this->params['breadcrumbs'] = [$this->title];
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>
