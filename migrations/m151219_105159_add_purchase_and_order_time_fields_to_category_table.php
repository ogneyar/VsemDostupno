<?php

use yii\db\Schema;
use yii\db\Migration;

class m151219_105159_add_purchase_and_order_time_fields_to_category_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%category}}', 'purchase_timestamp', Schema::TYPE_TIMESTAMP . " COMMENT 'Дата и время закупки'");
        $this->addColumn('{{%category}}', 'order_timestamp', Schema::TYPE_TIMESTAMP . " COMMENT 'Дата и время последних заказов'");
    }

    public function down()
    {
        $this->dropColumn('{{%category}}', 'purchase_timestamp');
        $this->dropColumn('{{%category}}', 'order_timestamp');
    }
}
