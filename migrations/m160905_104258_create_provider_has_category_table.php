<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `provider_has_category`.
 */
class m160905_104258_create_provider_has_category_table extends Migration
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

        $this->createTable('{{%provider_has_category}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'provider_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор поставщика'",
            'category_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор категории'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_provider_has_category_key (provider_id, category_id)',
        ], $tableOptions . " COMMENT 'Категория поставщика'");

        $this->createIndex('idx_provider_has_category_id', '{{%provider_has_category}}', 'id');
        $this->createIndex('idx_provider_has_category_provider_id', '{{%provider_has_category}}', 'provider_id');
        $this->createIndex('idx_provider_has_category_category_id', '{{%provider_has_category}}', 'category_id');

        $this->addForeignKey('fk_provider_has_category_provider_id', '{{%provider_has_category}}', 'provider_id', '{{%provider}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_provider_has_category_category_id', '{{%provider_has_category}}', 'category_id', '{{%category}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_provider_has_category_provider_id', '{{%provider_has_category}}');
        $this->dropForeignKey('fk_provider_has_category_category_id', '{{%provider_has_category}}');

        $this->dropIndex('idx_provider_has_category_id', '{{%provider_has_category}}');
        $this->dropIndex('idx_provider_has_category_provider_id', '{{%provider_has_category}}');
        $this->dropIndex('idx_provider_has_category_category_id', '{{%provider_has_category}}');

        $this->dropTable('{{%provider_has_category}}');
    }
}
