<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `document`.
 */
class m160907_084614_create_document_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%document}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'file' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Путь к файлу'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Документ'");

        $this->createIndex('idx_document_id', '{{%document}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex('idx_document_id', '{{%document}}');

        $this->dropTable('{{%document}}');
    }
}
