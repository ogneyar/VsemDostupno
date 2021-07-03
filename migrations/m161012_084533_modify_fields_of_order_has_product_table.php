<?php

use yii\db\Schema;
use yii\db\Migration;

class m161012_084533_modify_fields_of_order_has_product_table extends Migration
{
    public function up()
    {
        $this->renameColumn('{{%order_has_product}}', 'purchase_at', 'purchase_timestamp');
        $this->addColumn('{{%order_has_product}}', 'order_timestamp', Schema::TYPE_TIMESTAMP . " DEFAULT 0 COMMENT 'Дата и время последних заказов'");
        $this->addColumn('{{%order_has_product}}', 'purchase', Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Закупка'");
        $this->execute('UPDATE {{%order_has_product}} SET order_timestamp = purchase_timestamp');
    }

    public function down()
    {
        $this->renameColumn('{{%order_has_product}}', 'purchase_timestamp', 'purchase_at');
        $this->dropColumn('{{%order_has_product}}', 'order_timestamp');
        $this->dropColumn('{{%order_has_product}}', 'purchase');
    }
}
