<?php

use yii\db\Schema;
use yii\db\Migration;

class m151114_195034_create_email_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%email}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'type' => "ENUM('text', 'html') NOT NULL COMMENT 'Тип письма'",
            'subject' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Тема'",
            'body' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Содержание'",
            'UNIQUE KEY unique_name_key (name)',
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Письмо'");

        $this->createIndex('idx_email_id', '{{%email}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_email_id', '{{%email}}');

        $this->dropTable('{{%email}}');
    }
}
