<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `account_log`.
 */
class m160614_123544_create_account_log_table extends Migration
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

        $this->createTable('{{%account_log}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL COMMENT 'Дата операции'",
            'from_user_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор пользователя отправителя'",
            'to_user_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор пользователя получателя'",
            'account_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор счета'",
            'message' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Сообщение'",
            'amount' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Сумма'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Журнал операций со счетом'");

        $this->createIndex('idx_account_log_id', '{{%account_log}}', 'id');
        $this->createIndex('idx_from_user_id', '{{%account_log}}', 'from_user_id');
        $this->createIndex('idx_to_user_id', '{{%account_log}}', 'to_user_id');
        $this->createIndex('idx_account_id', '{{%account_log}}', 'account_id');

        $this->addForeignKey('fk_from_user_id', '{{%account_log}}', 'from_user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_to_user_id', '{{%account_log}}', 'to_user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_account_id', '{{%account_log}}', 'account_id', '{{%account}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_from_user_id', '{{%account_log}}');
        $this->dropForeignKey('fk_to_user_id', '{{%account_log}}');
        $this->dropForeignKey('fk_account_id', '{{%account_log}}');

        $this->dropIndex('idx_account_log_id', '{{%account_log}}');
        $this->dropIndex('idx_from_user_id', '{{%account_log}}');
        $this->dropIndex('idx_to_user_id', '{{%account_log}}');
        $this->dropIndex('idx_account_id', '{{%account_log}}');

        $this->dropTable('{{%account_log}}');
    }
}
