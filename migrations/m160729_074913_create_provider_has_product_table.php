<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `provider_has_product_table`.
 */
class m160729_074913_create_provider_has_product_table extends Migration
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

        $this->createTable('{{%provider_has_product}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'provider_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор поставщика'",
            'product_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор товара'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_provider_has_product_key (provider_id, product_id)',
        ], $tableOptions . " COMMENT 'Товар поставщика'");

        $this->createIndex('idx_provider_has_product_id', '{{%provider_has_product}}', 'id');
        $this->createIndex('idx_provider_has_product_provider_id', '{{%provider_has_product}}', 'provider_id');
        $this->createIndex('idx_provider_has_product_product_id', '{{%provider_has_product}}', 'product_id');

        $this->addForeignKey('fk_provider_has_product_provider_id', '{{%provider_has_product}}', 'provider_id', '{{%provider}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_provider_has_product_product_id', '{{%provider_has_product}}', 'product_id', '{{%product}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_provider_has_product_provider_id', '{{%provider_has_product}}');
        $this->dropForeignKey('fk_provider_has_product_product_id', '{{%provider_has_product}}');

        $this->dropIndex('idx_provider_has_product_id', '{{%provider_has_product}}');
        $this->dropIndex('idx_provider_has_product_provider_id', '{{%provider_has_product}}');
        $this->dropIndex('idx_provider_has_product_product_id', '{{%provider_has_product}}');

        $this->dropTable('{{%provider_has_product}}');
    }
}
