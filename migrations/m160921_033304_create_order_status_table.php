<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `order_status`.
 */
class m160921_033304_create_order_status_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_status}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'type' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Тип'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_type_key (type)',
        ], $tableOptions . " COMMENT 'Статус заказа'");

        $this->createIndex('idx_order_status_id', '{{%order_status}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx_order_status_id', '{{%order_status}}');

        $this->dropTable('{{%order_status}}');
    }
}
