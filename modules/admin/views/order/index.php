<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\dropdown\DropdownX;
use yii\helpers\ArrayHelper;
use app\models\Order;
use app\models\ProviderNotification;
use app\models\Provider;
use app\models\ProductFeature;
use app\models\User;

$this->title = 'Заказы на склад';
$this->params['breadcrumbs'][] = $this->title;
$delete_action = Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? 'delete-stock' : 'admin-delete';

$script = <<<JS
$(function () {
    $("#check-all").change(function() {
        if (this.checked) {
            $(".check_date").prop('checked', true);
        } else {
            $(".check_date").prop('checked', false);
        }
    });
})
JS;
$this->registerJs($script, $this::POS_END);
?>
<div class="member-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <table class="table table-bordered">
        <thead>
            <?php if (Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN): ?>
                <th><input type="checkbox" id="check-all"></th>
            <?php endif; ?>
            <th style="vertical-align: top;">Дата</th>
            <th></th>
        </thead>
        <tbody>
            <?php foreach ($dates as $date): ?>
                <tr>
                    <?php if (Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN): ?>
                        <td>
                            <input type="checkbox" class="check_date" data-date="<?= date('Y-m-d', strtotime($date['end'])) ?>">
                        </td>
                    <?php endif; ?>
                    <td>
                        <a href="<?= Url::to(['/admin/order/date', 'date' => date('Y-m-d', strtotime($date['end']))]); ?>"><?= date('d.m.Y', strtotime($date['end'])); ?></a>
                    </td>
                    <td>
                        <a href="<?= Url::to([$delete_action, 'date' => date('Y-m-d', strtotime($date['end']))]) ?>" title="Удалить" data-pjax="0" data-method="post" data-confirm="Вы уверены что хотите удалить заказ?">
                            <span class="glyphicon glyphicon-trash"></span>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN): ?>
        <?= Html::beginTag('div', ['class'=>'dropdown']) .
                Html::button('Действия с выбранными <span class="caret"></span>', [
                    'type'=>'button',
                    'class'=>'btn btn-default',
                    'data-toggle'=>'dropdown'
                ]) .
                DropdownX::widget([
                'items' => [
                    [
                        'label' => 'Удалить',
                        'url' => 'javascript:void(0)',
                        'linkOptions' => [
                            'onclick' => 'deleteCheckedOrdersStock();',
                        ],
                    ],
                ],
            ]) .
            Html::endTag('div');
        ?>
    <?php endif; ?>
</div>