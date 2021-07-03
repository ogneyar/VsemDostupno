<?php

use yii\db\Schema;
use yii\db\Migration;

class m151114_194622_create_page_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%page}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'visibility'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Видимость'",
            'slug' => Schema::TYPE_STRING . "(255) COMMENT 'Заголовок для URL'",
            'title' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Заголовок'",
            'content' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Содержимое'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Страница'");

        $this->createIndex('idx_page_id', '{{%page}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_page_id', '{{%page}}');

        $this->dropTable('{{%page}}');
    }
}
