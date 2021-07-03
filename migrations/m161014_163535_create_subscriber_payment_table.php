<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `subscriber_payment`.
 */
class m161014_163535_create_subscriber_payment_table extends Migration
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

        $this->createTable('{{%subscriber_payment}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'user_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор пользователя'",
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL DEFAULT NOW() COMMENT 'Дата и время платежа'",
            'amount' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Сумма'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Абонентская плата'");

        $this->createIndex('idx_subscriber_payment_id', '{{%subscriber_payment}}', 'id');
        $this->createIndex('idx_subscriber_payment_user_id', '{{%subscriber_payment}}', 'user_id');

        $this->addForeignKey('fk_subscriber_payment_user_id', '{{%subscriber_payment}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_subscriber_payment_user_id', '{{%subscriber_payment}}');

        $this->dropIndex('idx_subscriber_payment_id', '{{%subscriber_payment}}');
        $this->dropIndex('idx_subscriber_payment_user_id', '{{%subscriber_payment}}');

        $this->dropTable('{{%subscriber_payment}}');
    }
}
