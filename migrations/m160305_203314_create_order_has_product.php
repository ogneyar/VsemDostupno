<?php

use yii\db\Schema;
use yii\db\Migration;

class m160305_203314_create_order_has_product extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order_has_product}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'order_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор заказа'",
            'product_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор товара'",
            'purchase_at' => Schema::TYPE_TIMESTAMP . " DEFAULT 0 COMMENT 'Дата и время закупки'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'price' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Цена'",
            'quantity' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Количество'",
            'total' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Стоимость'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Товар заказа'");

        $this->createIndex('idx_order_has_product_id', '{{%order_has_product}}', 'id');
        $this->createIndex('idx_order_has_product_order_id', '{{%order_has_product}}', 'order_id');
        $this->createIndex('idx_order_has_product_product_id', '{{%order_has_product}}', 'product_id');

        $this->addForeignKey('fk_order_has_product_order_id', '{{%order_has_product}}', 'order_id', '{{%order}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}', 'product_id', '{{%product}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_order_has_product_order_id', '{{%order_has_product}}');
        $this->dropForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}');

        $this->dropIndex('idx_order_has_product_id', '{{%order_has_product}}');
        $this->dropIndex('idx_order_has_product_order_id', '{{%order_has_product}}');
        $this->dropIndex('idx_order_has_product_product_id', '{{%order_has_product}}');

        $this->dropTable('{{%order_has_product}}');
    }
}
