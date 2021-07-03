<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `service`.
 */
class m160904_225805_create_service_table extends Migration
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

        $this->createTable('{{%service}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'visibility'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Видимость'",
            'published'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Опубликованная'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'description' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Описание'",
            'price' => Schema::TYPE_MONEY . "(19,2) COMMENT 'Цена'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Услуга'");

        $this->createIndex('idx_service_id', '{{%service}}', 'id');
        $this->createIndex('idx_service_user_id', '{{%service}}', 'user_id');

        $this->addForeignKey('fk_service_user_id', '{{%service}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_service_user_id', '{{%service}}');

        $this->dropIndex('idx_service_id', '{{%service}}');
        $this->dropIndex('idx_service_user_id', '{{%service}}');

        $this->dropTable('{{%service}}');
    }
}
