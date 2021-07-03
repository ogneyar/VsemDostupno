<?php

use yii\db\Schema;
use yii\db\Migration;

class m160312_084837_add_inventory_field_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'inventory', Schema::TYPE_INTEGER . "(11) COMMENT 'Количество'");
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'inventory');
    }
}
