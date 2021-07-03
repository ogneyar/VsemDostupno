<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062508_create_partner_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'city_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор города'",
            'name' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Название"',
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_user_id_key (user_id)',
            'UNIQUE KEY unique_city_name (city_id, name)',
        ], $tableOptions . ' COMMENT "Партнер"');

        $this->createIndex('idx_partner_id', '{{%partner}}', 'id');
        $this->createIndex('idx_partner_user_id', '{{%partner}}', 'user_id');
        $this->createIndex('idx_partner_city_id', '{{%partner}}', 'city_id');

        $this->addForeignKey('fk_partner_user_id', '{{%partner}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_partner_city_id', '{{%partner}}', 'city_id', '{{%city}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_partner_user_id', '{{%partner}}');
        $this->dropForeignKey('fk_partner_city_id', '{{%partner}}');

        $this->dropIndex('idx_partner_id', '{{%partner}}');
        $this->dropIndex('idx_partner_user_id', '{{%partner}}');
        $this->dropIndex('idx_partner_city_id', '{{%partner}}');

        $this->dropTable('{{%partner}}');
    }
}
