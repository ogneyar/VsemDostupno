<?php

use yii\grid\GridView;
use yii\helpers\Url;
use kartik\helpers\Html;
use kartik\tabs\TabsX;
use kartik\dropdown\DropdownX;
use kartik\icons\Icon;
use app\models\Account;

/* @var $this yii\web\View */
$this->title = $title;
$this->params['breadcrumbs'] = [$this->title];
?>

<?= Html::pageHeader(Html::encode($this->title)) ?>

<?php if (!empty($myAccounts)): ?>
    <?= Html::tag('h2', 'Личные счета') ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="col-md-8">Счет</th>
                        <th class="col-md-2">Остаток</th>
                        <th class="col-md-2"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($myAccounts as $account): ?>
                    <tr>
                        <td class="vert-align"><?= Html::encode($account['name']) ?></td>
                        <td class="text-center vert-align"><?= $account['account']->total ?></td>
                        <td class="text-center vert-align">
                            <?php if ($account['actionEnable']): ?>
                                <?= Html::beginTag('div', ['class'=>'dropdown']) .
                                Html::button('Действия <span class="caret"></span>', [
                                    'type'=>'button',
                                    'class'=>'btn btn-default',
                                    'data-toggle'=>'dropdown'
                                ]) .
                                DropdownX::widget([
                                    'items' => [
                                        [
                                            'label' => Icon::show('user') . ' Перевести пользователю сайта',
                                            'url' => Url::to(['/profile/account/transfer']),
                                        ],
                                    ],
                                    'encodeLabels' => false,
                                ]) .
                                Html::endTag('div') ?>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>

<?php if (!empty($groupAccounts)): ?>
    <?= Html::tag('h2', 'Счета группы') ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="col-md-8">Счет</th>
                        <th class="col-md-2">Остаток</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($groupAccounts as $account): ?>
                    <tr>
                        <td class="vert-align"><?= Html::encode($account['name']) ?></td>
                        <td class="text-center vert-align"><?= $account['account']->total ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>

<?php if (!empty($fraternityAccount)): ?>
    <?= Html::tag('h2', 'Фонд содружества') ?>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="col-md-8">Счет</th>
                        <th class="col-md-2">Остаток</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fraternityAccount as $account): ?>
                    <tr>
                        <td class="vert-align"><?= Html::encode($account['name']) ?></td>
                        <td class="text-center vert-align"><?= $account['account']->total ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>

<?= Html::tag('h2', 'Детализация по счетам') ?> 
<?php
    $items = [];
    foreach (array_merge($myAccounts, $groupAccounts) as $account) {
        if (!isset($account['dataProvider'])) {
            continue;
        }

        $items[] = [
            'label' => $account['name'],
            'content' => GridView::widget([
                'dataProvider' => $account['dataProvider'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'created_at',
                    'amount',
                    'fromUserFullName',
                    'toUserFullName',
                    'message',
                ],
            ]),
            'active' => $accountType == $account['account']->type,
        ];
    }
    echo TabsX::widget(['items' => $items]);
?>
