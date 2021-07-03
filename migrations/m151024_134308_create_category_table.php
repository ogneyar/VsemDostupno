<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_134308_create_category_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%category}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'photo_id' => Schema::TYPE_INTEGER . ' COMMENT "Идентификатор изображения"',
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'description' => Schema::TYPE_TEXT . " COMMENT 'Описание'",
            'root' => Schema::TYPE_INTEGER . ' COMMENT "Корень"',
            'left' => Schema::TYPE_INTEGER . ' COMMENT "Левый узел"',
            'right' => Schema::TYPE_INTEGER . ' COMMENT "Правый узел"',
            'level' => Schema::TYPE_INTEGER . ' COMMENT "Уровень"',
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Категория'");

        $this->createIndex('idx_category_id', '{{%category}}', 'id');
        $this->createIndex('idx_left', '{{%category}}', 'left');
        $this->createIndex('idx_right', '{{%category}}', 'right');
        $this->createIndex('idx_level', '{{%category}}', 'level');

        $this->addForeignKey('fk_category_photo_id', '{{%category}}', 'photo_id', '{{%photo}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_category_id', '{{%category}}');
        $this->dropIndex('idx_left', '{{%category}}');
        $this->dropIndex('idx_right', '{{%category}}');
        $this->dropIndex('idx_level', '{{%category}}');

        $this->dropForeignKey('fk_category_photo_id', '{{%category}}');

        $this->dropTable('{{%category}}');
    }
}
