<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `account`.
 */
class m160614_123420_create_account_table extends Migration
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

        $this->createTable('{{%account}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'type' => "ENUM('deposit', 'bonus') NOT NULL COMMENT 'Тип счета'",
            'total' => Schema::TYPE_MONEY . "(19,2) NOT NULL DEFAULT 0 COMMENT 'Сумма'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Счет'");

        $this->createIndex('idx_account_id', '{{%account}}', 'id');
        $this->createIndex('idx_user_id', '{{%account}}', 'user_id');

        $this->addForeignKey('fk_user_id', '{{%account}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_user_id', '{{%account}}');

        $this->dropIndex('idx_account_id', '{{%account}}');
        $this->dropIndex('idx_user_id', '{{%account}}');

        $this->dropTable('{{%account}}');
    }
}
