<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use wbraganca\fancytree\FancytreeWidget;
use app\models\Category;
use app\models\City;
use kartik\select2\Select2;
use kartik\typeahead\Typeahead;

$items_tare = [
    'с/бут.' => 'с/бут.',
    'п/бут.' => 'п/бут.',
    'c/бан.' => 'c/бан.',
    'ж/бан.' => 'ж/бан.',
    'п/к.' => 'п/к.',
    'кор/корт.' => 'кор/корт.',
    'п/п.' => 'п/п.',
    'п/м.' => 'п/м.',
    'мешок' => 'мешок',
    '' => ''
];

$items_meas = [
    'кг.' => 'кг.',
    'л.' => 'л.',
    'шт.' => 'шт.',
    'гр.' => 'гр.',
    'мл.' => 'мл.',
    'мг.' => 'мг.',
];
?>

<div class="provider-form">
    <div class="stockbody-1">
        <input type="hidden" name="product_exists" id="product-exists" value="0">
        <?php if (count($product->productFeatures) > 0): ?>
            <span>Имеющиеся на складе:</span>
            <br />
            <?php foreach ($product->productFeatures as $val): ?>
                <a href="javascript:void(0);" data-id="<?= $val->id; ?>" class="avail-product" onclick="set_product_data(this);">
                    <?php if ($val->is_weights == 1): ?>
                        <?= 'Разновес в ' . $val->tare . ' по ' . $val->volume . ' ' . $val->measurement . ' общим количеством ' . $val->quantity . ' ' . $val->measurement . ' по цене ' . $val->productPrices[0]->purchase_price . ' руб. за ' . $val->measurement ?>
                    <?php else: ?>
                        <?= $val->tare . ' ' . $val->volume . ' ' . $val->measurement . ' в количестве ' . $val->quantity . ' шт. по цене ' . $val->productPrices[0]->purchase_price . ' руб.'; ?>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="javascript:void(0);" data-id="0" class="avail-product" onclick="set_product_data(this);">Добавить</a>
            <br><br>
            <div id="stock-inner-exists" style="display: none;">
                <input type="hidden" name="is_weights_ex" id="is_weights_ex" value="">
                <div class="form-group">
                    <label for="tare">Тара</label>
                    <?= Html::textInput('tare_ex', null, ['class' => 'form-control', 'id' => 'tare-ex', 'readonly' => true]); ?>
                </div>

                <div class="form-group">
                    <label for="weight">Масса/Объём</label>
                    <?= Html::textInput('volume_ex', null, ['class' => 'form-control', 'id' => 'volume-ex', 'readonly' => true]); ?>
                </div>

                <div class="form-group">
                    <label for="measurement">Ед. измерения</label>
                    <?= Html::textInput('measurement_ex', null, ['class' => 'form-control', 'id' => 'measurement-ex', 'readonly' => true]); ?>
                </div>

                <div class="form-group">
                    <label for="count">Количество</label>
                    <?= Html::textInput('count_ex', null, ['class' => 'form-control', 'id' => 'count-ex']); ?>
                </div>

                <div class="form-group">
                    <label for="summ">Сумма за ед./т.</label>
                    <?= Html::textInput('summ_ex', null, ['class' => 'form-control', 'id' => 'summ-ex', 'readonly' => true]); ?>
                </div>
                
                <div class="form-group">
                    <?= Html::checkbox('new_price', false, ['id' => 'new-price', 'onchange' => 'toggleNewPrice(this)']); ?>
                    <label for="new-price">Принять по новой цене</label>
                </div>

                <div class="form-group">
                    <?= Html::checkbox('deposit_ex', false, ['id' => 'deposit']); ?>
                    <label for="deposit">Зачислять на лицевой счёт</label>
                </div>
                
                <div class="form-group">
                    <label for="comment">Комментарий</label>
                    <?= Html::textarea('comment_ex', '', ['class' => 'form-control', 'id' => 'comment']); ?>
                </div>
            </div>
            <div id="stock-inner-new" style="display: none;">
                <div class="form-group">
                    <?= Html::checkbox('is_weights', false, ['id' => 'is_weights', 'onchange' => 'changeIsWeights(this);']); ?>
                    <label for="is_weights">Разновес/Упаковка</label>
                </div>
                <div class="form-group">
                    <label for="tare">Тара</label>
                    <?= Html::dropDownList(
                        'tare',
                        '',
                        $items_tare,
                        ['class' => 'form-control', 'id' => 'tare']
                    ); ?>
                </div>

                <div class="form-group">
                    <label for="weight" id="weight-lbl">Масса/Объём</label>
                    <?= Html::textInput('volume', null, ['class' => 'form-control', 'id' => 'volume']); ?>
                </div>

                <div class="form-group">
                    <label for="measurement">Ед. измерения</label>
                    <?= Html::dropDownList(
                        'measurement',
                        '',
                        $items_meas,
                        ['class' => 'form-control', 'id' => 'measurement']
                    ); ?>
                </div>

                <div class="form-group">
                    <label for="count" id="count-lbl">Количество</label>
                    <?= Html::textInput('count', null, ['class' => 'form-control', 'id' => 'count']); ?>
                </div>

                <div class="form-group">
                    <label for="summ" id="summ-lbl">Сумма за ед./т.</label>
                    <?= Html::textInput('summ', null, ['class' => 'form-control', 'id' => 'summ']); ?>
                </div>

                <div class="form-group">
                    <?= Html::checkbox('deposit', false, ['id' => 'deposit']); ?>
                    <label for="deposit">Зачислять на лицевой счёт</label>
                </div>
                
                <div class="form-group">
                    <label for="comment">Комментарий</label>
                    <?= Html::textarea('comment', '', ['class' => 'form-control', 'id' => 'comment']); ?>
                </div>
            </div>
        <?php else: ?>
            <div class="form-group">
                <?= Html::checkbox('is_weights', false, ['id' => 'is_weights', 'onchange' => 'changeIsWeights(this);']); ?>
                <label for="is_weights">Разновес/Упаковка</label>
            </div>
            <div class="form-group">
                <label for="tare">Тара</label>
                <?= Html::dropDownList(
                    'tare',
                    '',
                    $items_tare,
                    ['class' => 'form-control', 'id' => 'tare']
                ); ?>
            </div>

            <div class="form-group">
                <label for="weight" id="weight-lbl">Масса/Объём</label>
                <?= Html::textInput('volume', null, ['class' => 'form-control', 'id' => 'volume']); ?>
            </div>

            <div class="form-group">
                <label for="measurement">Ед. измерения</label>
                <?= Html::dropDownList(
                    'measurement',
                    '',
                    $items_meas,
                    ['class' => 'form-control', 'id' => 'measurement']
                ); ?>
            </div>

            <div class="form-group">
                <label for="count" id="count-lbl">Количество</label>
                <?= Html::textInput('count', null, ['class' => 'form-control', 'id' => 'count']); ?>
            </div>

            <div class="form-group">
                <label for="summ" id="summ-lbl">Сумма за ед./т.</label>
                <?= Html::textInput('summ', null, ['class' => 'form-control', 'id' => 'summ']); ?>
            </div>

            <div class="form-group">
                <?= Html::checkbox('deposit', false, ['id' => 'deposit']); ?>
                <label for="deposit">Зачислять на лицевой счёт</label>
            </div>
            
            <div class="form-group">
                <label for="comment">Комментарий</label>
                <?= Html::textarea('comment', '', ['class' => 'form-control', 'id' => 'comment']); ?>
            </div>
        <?php endif; ?>
    </div>


</div>
