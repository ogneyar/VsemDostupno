<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062500_create_city_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%city}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Город'");

        $this->createIndex('idx_city_id', '{{%city}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_city_id', '{{%city}}');

        $this->dropTable('{{%city}}');
    }
}
