<?php

use yii\db\Schema;
use yii\db\Migration;

class m160306_201205_modify_category_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%category}}', 'purchase_timestamp', Schema::TYPE_TIMESTAMP . " DEFAULT 0 COMMENT 'Дата и время закупки'");
        $this->alterColumn('{{%category}}', 'order_timestamp', Schema::TYPE_TIMESTAMP . " DEFAULT 0 COMMENT 'Дата и время последних заказов'");
    }

    public function down()
    {
        $this->alterColumn('{{%category}}', 'purchase_timestamp', Schema::TYPE_TIMESTAMP . " COMMENT 'Дата и время закупки'");
        $this->alterColumn('{{%category}}', 'order_timestamp', Schema::TYPE_TIMESTAMP . " COMMENT 'Дата и время последних заказов'");
    }
}
