<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `category_has_service`.
 */
class m160904_232345_create_category_has_service_table extends Migration
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

        $this->createTable('{{%category_has_service}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'category_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор категории'",
            'service_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор услуги'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_category_has_service_key (category_id, service_id)',
        ], $tableOptions . " COMMENT 'Услуга категории'");

        $this->createIndex('idx_category_has_service_id', '{{%category_has_service}}', 'id');
        $this->createIndex('idx_category_has_service_category_id', '{{%category_has_service}}', 'category_id');
        $this->createIndex('idx_category_has_service_service_id', '{{%category_has_service}}', 'service_id');

        $this->addForeignKey('fk_category_has_service_category_id', '{{%category_has_service}}', 'category_id', '{{%category}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_category_has_service_service_id', '{{%category_has_service}}', 'service_id', '{{%service}}', 'id', 'CASCADE', 'CASCADE');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_category_has_service_category_id', '{{%category_has_service}}');
        $this->dropForeignKey('fk_category_has_service_service_id', '{{%category_has_service}}');

        $this->dropIndex('idx_category_has_service_id', '{{%category_has_service}}');
        $this->dropIndex('idx_category_has_service_category_id', '{{%category_has_service}}');
        $this->dropIndex('idx_category_has_service_service_id', '{{%category_has_service}}');

        $this->dropTable('{{%category_has_service}}');
    }
}
