<?php

use yii\db\Schema;
use yii\db\Migration;

class m161011_092014_change_quantity_type_of_order_has_product_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%order_has_product}}', 'quantity', Schema::TYPE_DECIMAL . "(19,2) NOT NULL COMMENT 'Количество'");
    }

    public function down()
    {
        $this->alterColumn('{{%order_has_product}}', 'quantity', Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Количество'");
    }
}
