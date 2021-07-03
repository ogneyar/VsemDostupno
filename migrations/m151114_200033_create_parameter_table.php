<?php

use yii\db\Schema;
use yii\db\Migration;

class m151114_200033_create_parameter_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%parameter}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'name' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Название"',
            'value' => Schema::TYPE_TEXT . ' NOT NULL COMMENT "Значние"',
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_name_key (name)',
        ], $tableOptions . ' COMMENT "Параметр"');

        $this->createIndex('idx_parameter_id', '{{%parameter}}', 'id');
    }

    public function down()
    {
        $this->dropIndex('idx_parameter_id', '{{%parameter}}');

        $this->dropTable('{{%parameter}}');
    }
}
