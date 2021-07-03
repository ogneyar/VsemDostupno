<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_063726_create_forgot_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%forgot}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'token' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Токен"',
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_user_id_key (user_id)',
        ], $tableOptions . ' COMMENT "Восстановление пароля пользователя"');

        $this->createIndex('idx_forgot_id', '{{%forgot}}', 'id');
        $this->createIndex('idx_forgot_user_id', '{{%forgot}}', 'user_id');

        $this->addForeignKey('fk_forgot_user_id', '{{%forgot}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_forgot_user_id', '{{%forgot}}');

        $this->dropIndex('idx_forgot_id', '{{%forgot}}');
        $this->dropIndex('idx_forgot_user_id', '{{%forgot}}');

        $this->dropTable('{{%forgot}}');
    }
}
