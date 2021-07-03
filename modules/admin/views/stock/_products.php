<?php
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\BaseHtml;

?>

<label class="control-label" for="stockhead-product">Наименование товара</label>
<select name="product-id" id="product-id" class="form-control" onchange="displayProductData(this);">
    <option value="0" selected disabled>Выберите товар</option>
    <?php foreach ($data as $cat => $val): ?>
        <optgroup label="<?= $cat; ?>">
            <?php foreach ($val as $p_id => $p_name): ?>
                <option value="<?= $p_id; ?>"><?= $p_name; ?></option>
            <?php endforeach; ?>
        </optgroup>
    <?php endforeach; ?>
</select>

