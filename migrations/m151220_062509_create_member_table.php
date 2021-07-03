<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062509_create_member_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%member}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'partner_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор партнера'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_user_id_key (user_id)',
        ], $tableOptions . ' COMMENT "Участник"');

        $this->createIndex('idx_member_id', '{{%member}}', 'id');
        $this->createIndex('idx_member_user_id', '{{%member}}', 'user_id');
        $this->createIndex('idx_member_partner_id', '{{%member}}', 'partner_id');

        $this->addForeignKey('fk_member_user_id', '{{%member}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_member_partner_id', '{{%member}}', 'partner_id', '{{%partner}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_member_user_id', '{{%member}}');
        $this->dropForeignKey('fk_member_partner_id', '{{%member}}');

        $this->dropIndex('idx_member_id', '{{%member}}');
        $this->dropIndex('idx_member_user_id', '{{%member}}');
        $this->dropIndex('idx_member_partner_id', '{{%member}}');

        $this->dropTable('{{%member}}');
    }
}
