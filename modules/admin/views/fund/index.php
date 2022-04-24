<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\dropdown\DropdownX;
use yii\bootstrap\Modal;

$this->title = 'Фонды';
$this->params['breadcrumbs'][] = $this->title;

$funds_select = [];
foreach ($dataProvider->getModels() as $fund) {
    $funds_select[$fund->id] = $fund->name;
}
?>
<div class="fund-box">
<div class="fund-left-box">
<div class="fund-index">
    <h1><?= Html::encode($this->title) ?></h1>
    
    
    <?php Pjax::begin(['id' => 'fund-name-pjax']); ?>
<?php
$script = <<<JS
    $(function () {
        let web = "/web";
        // let web = "";
        $("#fund-name-tbl tbody tr").click(function() {
            if ($(this).css('background-color') == 'rgb(157, 157, 157)') {
                $("#fund-name-tbl tr").css({'background-color' : '#fff'});
                $("#del-fund-btn").hide();
                $("#add-fund-cnt").hide();
                $("#save-fund-btn").hide();
                $("#cancel-fund-btn").hide();
                $("#add-fund-btn").show();
                $("#save-fund-btn").data('action', 'add').attr('data-action', 'add');
                $("#save-fund-btn").data('id', '').attr('data-id', '');
                $("#del-fund-btn").data('id', '').attr('data-id', '');
            } else {
                var attr = $(this).attr('data-key');
                if (typeof attr !== typeof undefined && attr !== false) {
                    $("#fund-name-tbl tr").css({'background-color' : '#fff'});
                    $(this).css({'background-color' : '#9d9d9d'});
                    $("#add-fund-btn").hide();
                    $("#del-fund-btn").show();
                    $("#add-fund-cnt").show();
                    $("#save-fund-btn").show();
                    $("#cancel-fund-btn").show();
                    $("#save-fund-btn").data('action', 'update').attr('data-action', 'update');
                    $("#save-fund-btn").data('id', $(this).data('key')).attr('data-id', $(this).data('key'));
                    $("#del-fund-btn").data('id', $(this).data('key')).attr('data-id', $(this).data('key'));
                    $.ajax({
                        url: web + "/admin/fund/get-fund",
                        type: "POST",
                        data: {id: $(this).data('key')},
                        success: function(response) {
                            var data = $.parseJSON(response);
                            
                            $("#fund-name").val(data.name);
                            $("#fund-percent").val(data.percent);
                        }
                    });
                }
            }
        });
    })
JS;
$this->registerJs($script, $this::POS_END);
?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => [
                'id' => 'fund-name-tbl',
                'class' => 'table table-bordered',
            ],
            'layout' => "{items}",
            'columns' => [
                [
                    'attribute' => 'name',
                    'headerOptions' => ['style' => 'min-width: 90%;']
                ],
                'percent',
            ],
        ]); ?>
    <?php Pjax::end(); ?>
    
    <div class="fund-buttons-container">
        <div class="fund-btns-row-1">
            <button id="add-fund-btn" type="button" class="btn btn-success">Добавить фонд</button>
            <button id="del-fund-btn" data-id="" type="button" class="btn btn-success" style="display: none;">Удалить фонд</button>
        </div>
        <div class="fund-btns-row-2">
            <button id="save-fund-btn" data-action="add" data-id="" type="button" class="btn btn-success" style="display: none;">Сохранить</button>
            <button id="cancel-fund-btn" type="button" class="btn btn-danger" style="display: none;">Отмена</button>
        </div>
    </div>
    <div class="add-fund-cnt" id="add-fund-cnt" style="display: none;">
        <input id="fund-name" type="text" class="form-control" style="width: 58%; display: inline-block;">
        <input id="fund-percent" type="text" class="form-control" style="width: 6.65%; display: inline-block;">
        
    </div>
</div>
<div class="fund-index-2">
    <h4>ПРОИЗВЕДЁННЫЕ ОТЧИСЛЕНИЯ</h4>
    
    <?php Pjax::begin(['id' => 'fund-deduction-pjax']); ?>
<?php
$script = <<<JS
    $(function () {
        $(".transfer-open").click(function() {
            $("#amount-from-input").val($(this).attr("data-fund"));
            $("#amount-to-input").val($(this).attr("data-fund"));
            $("#amount-to").val("");
            $("#amount-from").val("");
            $("#transfer-from-lbl").html('Из ' + $(this).attr("data-fund-name"));
            $("#transfer-to-lbl").html('В ' + $(this).attr("data-fund-name"));
        });
    })
JS;
$this->registerJs($script, $this::POS_END);
?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => [
                'class' => 'table table-bordered',
            ],
            'layout' => "{items}",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'headerOptions' => ['style' => 'min-width: 3%;']
                ],
                [
                    'attribute' => 'name',
                    'headerOptions' => ['style' => 'min-width: 55%;']
                ],
                [
                    'label' => '',
                    'attribute' => 'deduction_total'
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'headerOptions' => ['style' => 'min-width: 10%;'],
                    'template' => '{actions}',
                    'buttons' => [
                        'actions' => function ($url, $model) {
                            return Html::beginTag('div', ['class'=>'dropdown']) .
                                Html::button('Действия <span class="caret"></span>', [
                                    'type'=>'button',
                                    'class'=>'btn btn-default',
                                    'data-toggle'=>'dropdown'
                                ]) .
                                DropdownX::widget([
                                'items' => [
                                    [
                                        'label' => 'Списание',
                                        'url' => 'javascript:void(0);',
                                        'options' => [
                                            'data-toggle' => 'modal',
                                            'data-target' => '#transfer-from-modal',
                                            'data-fund' => $model->id,
                                            'data-fund-name' => $model->name,
                                            'class' => 'transfer-open'
                                        ]
                                    ],
                                    [
                                        'label' => 'Начисление',
                                        'url' => 'javascript:void(0);',
                                        'options' => [
                                            'data-toggle' => 'modal',
                                            'data-target' => '#transfer-to-modal',
                                            'data-fund' => $model->id,
                                            'data-fund-name' => $model->name,
                                            'class' => 'transfer-open'
                                        ]
                                    ],
                                    [
                                        'label' => 'Распределение',
                                        'url' => 'fund/distribute'
                                    ],
                                ],
                            ]) .
                            Html::endTag('div');
                        }
                    ],
                ],
            ],
        ]); ?>
    <?php Pjax::end(); ?>
</div>
</div>

<div class="fund-right-box">
    
    <div class="fund-buttons-container">
        <div class="fund-btns-row-1">
            <button id="add-fund-btn" type="button" class="btn btn-success">Добавить фонд</button>
            <button id="del-fund-btn" data-id="" type="button" class="btn btn-success" style="display: none;">Удалить фонд</button>
        </div>
        <div class="fund-btns-row-2">
            <button id="save-fund-btn" data-action="add" data-id="" type="button" class="btn btn-success" style="display: none;">Сохранить</button>
            <button id="cancel-fund-btn" type="button" class="btn btn-danger" style="display: none;">Отмена</button>
        </div>
        <div class="fund-btns-row-3">
            <div>
                <label>Общий баланс</label>
                <input disabled placeholder="" value="<?=$balance?>" />
            </div>
            <div
                style="display: flex; flex-direction: row; align-items: center;"
            >
                <label>Счёт ПО</label>
                <input disabled placeholder="" value="<?=$po?>" />
                <?php 
                    echo Html::beginTag('div', ['class'=>'dropdown']);
                    echo Html::button('Действия <span class="caret"></span>', [
                        'type'=>'button',
                        'class'=>'btn btn-default',
                        'data-toggle'=>'dropdown'
                    ]);
                    echo DropdownX::widget([
                        'items' => [
                            [
                                'label' => 'Списать сумму',
                                'url' => 'javascript:void(0);'
                            ],
                            [
                                'label' => 'Архив',
                                'url' => 'javascript:void(0);'
                            ],
                        ],
                    ]);
                    echo Html::endTag('div');
                ?>
            </div>
            <div
                style="display: flex; flex-direction: row; align-items: center;"
            >
                <label>Счёт содружества</label>
                <input disabled placeholder="" value="<?=$friend?>" />
                <div class='dropdown'>
                    <button class='btn btn-default' data-toggle='dropdown'>Действия <span class="caret"></span></button>
                    <?php echo DropdownX::widget([
                        'items' => [
                            [ 'label' => 'Списать сумму', 'url' => 'javascript:void(0);' ],
                            [ 'label' => 'Архив', 'url' => 'javascript:void(0);' ],
                        ],
                    ])?>
                </div>
            </div>

            <div
                style="display: flex; flex-direction: row; align-items: center;"
            >
                <label>Членские взносы</label>
                <input disabled placeholder="<?php 
                    if ($minus > 0) echo("-" . $minus); 
                    else echo($minus);
                ?>" style="width:100px;" />
                <input disabled placeholder="<?=$storage?>" style="width:120px;" />
                <div class='dropdown'>
                    <button class='btn btn-default' data-toggle='dropdown'>Действия <span class="caret"></span></button>
                    <?php echo DropdownX::widget([
                        'items' => [
                            [ 'label' => 'Списать сумму', 'url' => 'javascript:void(0);' ],
                            [ 'label' => 'Архив', 'url' => 'javascript:void(0);' ],
                        ],
                    ])?>
                </div>
            </div>
        </div>
    </div>
    <div class="add-fund-cnt" id="add-fund-cnt" style="display: none;">
        <input id="fund-name" type="text" class="form-control" style="width: 58%; display: inline-block;">
        <input id="fund-percent" type="text" class="form-control" style="width: 6.65%; display: inline-block;">
        
    </div>
</div>
</div>

<?php Modal::begin([
    'id' => 'transfer-from-modal',
    'options' => ['tabindex' => false,],
    'size' => Modal::SIZE_SMALL,
    'header' => '<h4>' . 'Списание средств' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . 'Закрыть' . '</a>
                 <button id="transfer-from-btn" class="btn btn-success" type="button" onclick="transferFundFrom()">' . 'Выполнить' . '</button>',
]); ?>
    
    <div class="form-group">
        <label id="transfer-from-lbl"></label>
    </div>
    
    <div class="form-group">
        <label for="weight">Сумма</label>
        <?= Html::textInput('amount', null, ['class' => 'form-control', 'id' => 'amount-to']); ?>
    </div>
    <input type="hidden" id="amount-from-input" value="">

<?php Modal::end(); ?>

<?php Modal::begin([
    'id' => 'transfer-to-modal',
    'options' => ['tabindex' => false,],
    'size' => Modal::SIZE_SMALL,
    'header' => '<h4>' . 'Начисление средств' . '</h4>',
    'footer' => '<a class="btn btn-default" data-dismiss="modal" aria-hidden="true">' . 'Закрыть' . '</a>
                 <button id="transfer-to-btn" class="btn btn-success" type="button" onclick="transferFundTo()">' . 'Выполнить' . '</button>',
]); ?>

    <div class="form-group">
        <label id="transfer-to-lbl"></label>
    </div>
    
    <div class="form-group">
        <label for="tare">Начислить из</label>
        <?= Html::dropDownList(
            'fund-from',
            '',
            $funds_select,
            ['class' => 'form-control', 'id' => 'fund-from-select']
        ); ?>
    </div>
    
    <div class="form-group">
        <label for="weight">Сумма</label>
        <?= Html::textInput('amount', null, ['class' => 'form-control', 'id' => 'amount-from']); ?>
    </div>
    <input type="hidden" id="amount-to-input" value="">

<?php Modal::end(); ?>