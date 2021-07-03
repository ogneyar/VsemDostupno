<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062505_create_user_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'role' => "ENUM('admin', 'member', 'partner') NOT NULL COMMENT 'Роль'",
            'disabled'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Отключен'",
            'email' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Емайл'",
            'phone' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Телефон'",
            'firstname' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Имя'",
            'lastname' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Фамилия'",
            'patronymic' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Отчество'",
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL DEFAULT NOW() COMMENT 'Дата и время создания'",
            'created_ip' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'IP-адрес создания'",
            'logged_in_at' => Schema::TYPE_TIMESTAMP . " COMMENT 'Дата и время входа'",
            'logged_in_ip' => Schema::TYPE_STRING . "(255) COMMENT 'IP-адрес входа'",
            'password' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Пароль'",
            'auth_key' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Ключ авторизации'",
            'access_token' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Токен доступа'",
            'UNIQUE KEY unique_user_email_key (email)',
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Пользователь'");

        $this->createIndex('idx_user_id', '{{%user}}', 'id');
        $this->createIndex('idx_user_email', '{{%user}}', 'email');
        $this->createIndex('idx_user_access_token', '{{%user}}', 'access_token');
    }

    public function down()
    {
        $this->dropIndex('idx_user_id', '{{%user}}');
        $this->dropIndex('idx_user_email', '{{%user}}');
        $this->dropIndex('idx_user_access_token', '{{%user}}');

        $this->dropTable('{{%user}}');
    }
}
