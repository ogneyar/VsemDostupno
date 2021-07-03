<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_131844_create_image_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%image}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'file' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Путь к файлу'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Изображение'");

        $this->createIndex('idx_image_id', '{{%image}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_image_id', '{{%image}}');

        $this->dropTable('{{%image}}');
    }
}
