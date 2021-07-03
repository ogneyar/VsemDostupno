<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `provider`.
 */
class m160726_123645_create_provider_table extends Migration
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

        $this->createTable('{{%provider}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'name' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Название"',
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_user_id_key (user_id)',
            'UNIQUE KEY unique_name (name)',
        ], $tableOptions . ' COMMENT "Поставщик"');

        $this->createIndex('idx_provider_id', '{{%provider}}', 'id');
        $this->createIndex('idx_provider_user_id', '{{%provider}}', 'user_id');

        $this->addForeignKey('fk_provider_user_id', '{{%provider}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_provider_user_id', '{{%provider}}');

        $this->dropIndex('idx_provider_id', '{{%provider}}');
        $this->dropIndex('idx_provider_user_id', '{{%provider}}');

        $this->dropTable('{{%provider}}');
    }
}
