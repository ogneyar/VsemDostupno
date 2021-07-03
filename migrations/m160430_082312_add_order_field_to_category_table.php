<?php

use yii\db\Schema;
use yii\db\Migration;

class m160430_082312_add_order_field_to_category_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%category}}', 'order', Schema::TYPE_INTEGER . " NOT NULL DEFAULT 0 COMMENT 'Порядок'");
    }

    public function down()
    {
        $this->dropColumn('{{%category}}', 'order');
    }
}
