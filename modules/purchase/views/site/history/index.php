<?php
use kartik\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\helpers\NumberColumn;
use app\models\ProductFeature;

$this->title = 'История моих закупок';
$this->params['breadcrumbs'] = [$this->title];
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>
<div class="order-index">
    <table class="table table-bordered">
        <thead>
            <th style="vertical-align: top;">Дата доставки</th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach ($purchases_date as $date): ?>
                <tr>
                    <td>
                        <a href="<?= Url::to(['details', 'date' => date('Y-m-d', strtotime($date['purchase_date']))]); ?>"><?= date('d.m.Y', strtotime($date['purchase_date'])); ?></a>
                    </td>
                    <td>
                        
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>