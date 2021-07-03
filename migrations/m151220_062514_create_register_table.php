<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062514_create_register_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%register}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'token' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Токен"',
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_user_id_key (user_id)',
        ], $tableOptions . ' COMMENT "Регистрация пользователя"');

        $this->createIndex('idx_register_id', '{{%register}}', 'id');
        $this->createIndex('idx_register_user_id', '{{%register}}', 'user_id');

        $this->addForeignKey('fk_register_user_id', '{{%register}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_register_user_id', '{{%register}}');

        $this->dropIndex('idx_register_id', '{{%register}}');
        $this->dropIndex('idx_register_user_id', '{{%register}}');

        $this->dropTable('{{%register}}');
    }
}
