<?php

use yii\db\Schema;
use yii\db\Migration;

class m151123_115952_add_purchase_info_parameter extends Migration
{
    public function up()
    {
        $this->BatchInsert('{{%parameter}}', ['name', 'value'], [
            ['purchase-info', 'В данный момент товара нет в наличие, но мы его купим, если наберем достаточное количество желающих. Добавляйте товар в корзину, если есть желание поучаствовать в закупке. Дату закупки и другую информацию смотрите в Описании товара.'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%parameter}}', ['name' => 'purchase-info']);
    }
}
