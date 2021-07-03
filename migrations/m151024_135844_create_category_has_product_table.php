<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_135844_create_category_has_product_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%category_has_product}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'category_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор категории'",
            'product_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор товара'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_category_has_product_key (category_id, product_id)',
        ], $tableOptions . " COMMENT 'Товар категории'");

        $this->createIndex('idx_category_has_product_id', '{{%category_has_product}}', 'id');
        $this->createIndex('idx_category_has_product_category_id', '{{%category_has_product}}', 'category_id');
        $this->createIndex('idx_category_has_product_product_id', '{{%category_has_product}}', 'product_id');

        $this->addForeignKey('fk_category_has_product_category_id', '{{%category_has_product}}', 'category_id', '{{%category}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_category_has_product_product_id', '{{%category_has_product}}', 'product_id', '{{%product}}', 'id', 'CASCADE', 'CASCADE');

    }

    public function down()
    {
        $this->dropForeignKey('fk_category_has_product_category_id', '{{%category_has_product}}');
        $this->dropForeignKey('fk_category_has_product_product_id', '{{%category_has_product}}');

        $this->dropIndex('idx_category_has_product_id', '{{%category_has_product}}');
        $this->dropIndex('idx_category_has_product_category_id', '{{%category_has_product}}');
        $this->dropIndex('idx_category_has_product_product_id', '{{%category_has_product}}');

        $this->dropTable('{{%category_has_product}}');
    }
}
