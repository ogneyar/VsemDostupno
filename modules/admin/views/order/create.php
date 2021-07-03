<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

$this->title = 'Добавить заказ';
$this->params['breadcrumbs'][] = $this->title;

$addProductUrl = Url::to(['/api/profile/admin/product/add']);
$editAccountUrl = Url::to(['/admin/user/account']);

$script = <<<JS
    function getOrderTotal() {
        var total = 0;

        $('.product-total').each(function() {
            total += parseFloat($(this).text());
        });

        return total.toFixed(2);
    }

    function updateOrderTotal() {
        $('#order-total').text('Итого: ' + getOrderTotal());
    }

    function deleteProduct(id) {
        if (confirm('Удалить товар из списка?')) {
            $('#product-' + id).remove();
        }

        updateOrderTotal();

        return false;
    }

    function addProduct(id, name, quantity, price, total, purchase_date) {
        if ($('#product-' + id).length) {
            $('#product-' + id + ' .product-name').text(name);
            $('#product-' + id + ' .product-quantity').text(quantity);
            $('#product-' + id + ' .product-price').text(price);
            $('#product-' + id + ' .product-total').text(total);
            if (purchase_date != "") {
                $('#product-' + id + ' .product-purchase-date').text(purchase_date);
            }
        } else {
            var purchase_str = "";
            if (purchase_date != "") {
                purchase_str = '<td class="product-purchase-date text-center">' + purchase_date + '</td>';
            }
            var row = '<tr id="product-' + id + '">' +
                '<td class="product-name">' + name + '</td>' +
                '<td class="product-quantity text-center">' + quantity + '</td>' +
                '<td class="product-price text-center">' + price + '</td>' +
                '<td class="product-total text-center">' + total + '</td>' +
                purchase_str +
                '<td class="text-center">' +
                    '<a href="#" onclick="return deleteProduct(' + id + ');" title="Удалить"><span class="glyphicon glyphicon-trash"></span></a>' +
                '</td>' +
            '</tr>';
            $('#product-list tbody').append(row);
        }
        $('#quantity').val(quantity);

        updateOrderTotal();
    }

    $(function () {
        updateOrderTotal();

        $('#edit-account').on('click', function() {
            var userId = $('#user-id').val();
            var url = '$editAccountUrl?id=' + userId;
            var win = window.open(url, '_blank');

            win.focus();

            return false;
        });

        $('#add-product').on('click', function() {
            var userId = $('#user-id').val();
            var productId = $('#product-id').val();
            var quantity = $('#quantity').val();

            if (!userId) {
                alert('Не введен покупатель!');
                return false;
            }

            if (!productId) {
                alert('Не введен товар!');
                return false;
            }

            if (!quantity) {
                alert('Не введено количество!');
                $('#quantity').focus();
                return false;
            }

            $.ajax({
                url: '$addProductUrl',
                type: 'POST',
                async: false,
                cache: false,
                timeout: 30000,
                data: {
                    ProductAddition: {
                        user_id: userId,
                        product_id: productId,
                        quantity: quantity,
                        is_purchase: $("#is_purchase").prop("checked")
                    }
                },
                success: function (data) {
                    addProduct(data.id, data.name, data.quantity, data.price, data.total, data.purchase_date);
                },
                error: function () {
                },
            });

            return false;
        });

        $('button[type="submit"]').on('click', function() {
            var userId = $('#user-id').val();
            var productList = [];

            $('#product-list tr[id]').each(function(index, value) {
                var id = $(this).attr('id');
                var quantity = $('#' + id + ' .product-quantity').text();

                id = id.replace(/^product-/, '');

                productList.push({'id': id, 'quantity': quantity});
            });
            productList = productList.length ? JSON.stringify(productList) : '';

            $('input[name="OrderForm[user_id]"]').val(userId);
            $('input[name="OrderForm[product_list]"]').val(productList);

            return true;
        });
        
        $("#is_purchase").change(function() {
            if (this.checked) {
                $("#purchase-date-th").show();
            } else {
                $("#purchase-date-th").hide();
            }
        });
    });
JS;
$this->registerJs($script, $this::POS_END);
?>

<div class="order-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'options' => [
            'name' => 'order',
        ],
    ]); ?>
    
    <?php if (Yii::$app->hasModule('purchase')): ?>
        <div class="form-group">
            <?= Html::checkbox('is_purchase', false, ['id' => 'is_purchase']); ?>
            <label for="is_purchase">Коллективная закупка</label>
        </div>
    <?php endif; ?>

    <h3>Информация о покупателе</h3>

    <div class="form-group field-orderform-product">
        <label class="control-label" for="orderform-product">Покупатель</label>
        <?= Select2::widget([
            'id' => 'user-id',
            'name' => 'user-id',
            'options' => ['placeholder' => 'Введите покупателя ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 1,
                'language' => substr(Yii::$app->language, 0, 2),
                'ajax' => [
                    'url' => Url::to(['/api/profile/admin/user/search']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(user) { return user.text; }'),
                'templateSelection' => new JsExpression('function (user) { return user.text; }'),
            ],
            'pluginEvents' => [
                'select2:select' => new JsExpression('function() { $("#edit-account").prop("disabled", false); }'),
                'select2:unselect' => new JsExpression('function() { $("#edit-account").prop("disabled", true); }'),
            ],
        ]) ?>
    </div>

    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <div class="text-right form-group field-orderform-edit-account">
        <?= Html::button('Пополнить счет', [
            'id' => 'edit-account',
            'class' => 'btn btn-info',
            'disabled' => 'disabled',
        ]) ?>
    </div>

    <h3>Товары</h3>

    <div class="form-group field-orderform-product">
        <label class="control-label" for="orderform-product">Товар</label>
        <?= Select2::widget([
            'id' => 'product-id',
            'name' => 'product-id',
            'options' => ['placeholder' => 'Введите товар ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'language' => substr(Yii::$app->language, 0, 2),
                'ajax' => [
                    'url' => Url::to(['/api/profile/admin/product/search']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term, c:$("#is_purchase").prop("checked")}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(product) { return product.text; }'),
                'templateSelection' => new JsExpression('function (product) { return product.text; }'),
            ],
            'pluginEvents' => [
                'select2:select' => new JsExpression('function() { $("#add-product").prop("disabled", false); }'),
                'select2:unselect' => new JsExpression('function() { $("#add-product").prop("disabled", true); }'),
            ],
        ]) ?>
    </div>

    <div class="form-group field-orderform-quantity">
        <label class="control-label" for="orderform-quantity">Количество</label>
        <?= Html::textInput('quantity', null, [
            'id' => 'quantity',
            'name' => 'quantity',
            'placeholder' => 'Введите количество ...',
            'class' => 'form-control',
        ]) ?>
    </div>

    <div class="text-right form-group field-orderform-add-product">
        <?= Html::button('Добавить товар', [
            'id' => 'add-product',
            'class' => 'btn btn-info',
            'disabled' => 'disabled',
        ]) ?>
    </div>

    <div class="form-group field-orderform-list">
        <label class="control-label" for="orderform-list">Список</label>
        <table id="product-list" class="table table-bordered">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Количество</th>
                    <th>Цена</th>
                    <th>Всего</th>
                    <th id="purchase-date-th" style="display: none;">Дата закупки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="form-group field-orderform-order-total">
        <strong>
            <div id="order-total" class="text-right"></div>
        </strong>
    </div>

    <?= $form->field($model, 'product_list')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
