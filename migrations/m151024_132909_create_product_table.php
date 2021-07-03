<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_132909_create_product_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'visibility'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Видимость'",
            'featured'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Спецпредложение'",
            'recent'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Новый'",
            'purchase'=> Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Закупка'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'description' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Описание'",
            'price' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Цена'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Товар'");

        $this->createIndex('idx_product_id', '{{%product}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_product_id', '{{%product}}');

        $this->dropTable('{{%product}}');
    }
}
