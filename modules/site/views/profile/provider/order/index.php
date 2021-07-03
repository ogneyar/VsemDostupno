<?php
use kartik\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use app\helpers\NumberColumn;
use app\models\ProductFeature;

$this->title = 'Групповая закупка';
$this->params['breadcrumbs'] = [$this->title];
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>
<div class="order-index">
    <table class="table table-bordered">
        <thead>
            <th style="vertical-align: top;">Дата</th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach ($purchases_date as $date): ?>
                <tr>
                    <td>
                        <a href="<?= Url::to(['date', 'date' => date('Y-m-d', strtotime($date['purchase_date']))]); ?>"><?= date('d.m.Y', strtotime($date['purchase_date'])); ?></a>
                    </td>
                    <td>
                        <a href="<?= Url::to(['delete', 'date' => date('Y-m-d', strtotime($date['purchase_date']))]) ?>" title="Удалить" data-pjax="0" data-method="post" data-confirm="Вы уверены что хотите удалить закупку?">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>