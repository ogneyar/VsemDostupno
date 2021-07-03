<?php

use yii\db\Schema;
use yii\db\Migration;

class m161021_130115_remove_template_table extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_template_document_id', '{{%template}}');

        $this->dropIndex('idx_template_id', '{{%template}}');

        $this->dropTable('{{%template}}');
    }

    public function down()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%template}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'document_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор документа'",
            'name' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Название'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Шаблон'");

        $this->createIndex('idx_template_id', '{{%template}}', 'id');

        $this->addForeignKey('fk_template_document_id', '{{%template}}', 'document_id', '{{%document}}', 'id', 'CASCADE', 'CASCADE');
    }
}
