<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `transfer`.
 */
class m160704_135325_create_transfer_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%transfer}}', [
            'id' => Schema::TYPE_INTEGER . '(11) NOT NULL AUTO_INCREMENT COMMENT "Идентификатор"',
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL COMMENT 'Дата операции'",
            'from_account_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор счета-источнка'",
            'to_account_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор счета-приемника'",
            'amount' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Сумма'",
            'message' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Сообщение'",
            'token' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Токен"',
            'PRIMARY KEY (id)',
        ], $tableOptions . ' COMMENT "Перевод"');

        $this->createIndex('idx_transfer_id', '{{%transfer}}', 'id');
        $this->createIndex('idx_transfer_to_account_id', '{{%transfer}}', 'to_account_id');
        $this->createIndex('idx_transfer_from_account_id', '{{%transfer}}', 'from_account_id');

        $this->addForeignKey('fk_transfer_to_account_id', '{{%transfer}}', 'to_account_id', '{{%account}}', 'id');
        $this->addForeignKey('fk_transfer_from_account_id', '{{%transfer}}', 'from_account_id', '{{%account}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_transfer_to_account_id', '{{%transfer}}');
        $this->dropForeignKey('fk_transfer_from_account_id', '{{%transfer}}');

        $this->dropIndex('idx_transfer_id', '{{%transfer}}');
        $this->dropIndex('idx_transfer_to_account_id', '{{%transfer}}');
        $this->dropIndex('idx_transfer_from_account_id', '{{%transfer}}');

        $this->dropTable('{{%transfer}}');
    }
}
