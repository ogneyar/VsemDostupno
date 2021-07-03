<?php

use yii\db\Schema;
use yii\db\Migration;

class m161021_125925_remove_partner_has_template_table extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_partner_has_template_partner_id', '{{%partner_has_template}}');
        $this->dropForeignKey('fk_partner_has_template_template_id', '{{%partner_has_template}}');

        $this->dropIndex('idx_partner_has_template_id', '{{%partner_has_template}}');
        $this->dropIndex('idx_partner_has_template_partner_id', '{{%partner_has_template}}');
        $this->dropIndex('idx_partner_has_template_template_id', '{{%partner_has_template}}');

        $this->dropTable('{{%partner_has_template}}');
    }

    public function down()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%partner_has_template}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'partner_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор парнера'",
            'template_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор шаблона'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_partner_has_template_key (partner_id, template_id)',
        ], $tableOptions . " COMMENT 'Шаблон партнера'");

        $this->createIndex('idx_partner_has_template_id', '{{%partner_has_template}}', 'id');
        $this->createIndex('idx_partner_has_template_partner_id', '{{%partner_has_template}}', 'partner_id');
        $this->createIndex('idx_partner_has_template_template_id', '{{%partner_has_template}}', 'template_id');

        $this->addForeignKey('fk_partner_has_template_partner_id', '{{%partner_has_template}}', 'partner_id', '{{%partner}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_partner_has_template_template_id', '{{%partner_has_template}}', 'template_id', '{{%template}}', 'id', 'CASCADE', 'CASCADE');
    }
}
