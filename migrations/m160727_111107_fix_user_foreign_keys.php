<?php

use yii\db\Migration;

class m160727_111107_fix_user_foreign_keys extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_order_user_id', '{{%order}}');

        $this->dropForeignKey('fk_user_id', '{{%account}}');
        $this->addForeignKey('fk_user_id', '{{%account}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->dropForeignKey('fk_from_user_id', '{{%account_log}}');
        $this->dropForeignKey('fk_to_user_id', '{{%account_log}}');
        $this->dropForeignKey('fk_account_id', '{{%account_log}}');
    }

    public function down()
    {
        $this->addForeignKey('fk_order_user_id', '{{%order}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->dropForeignKey('fk_user_id', '{{%account}}');
        $this->addForeignKey('fk_user_id', '{{%account}}', 'user_id', '{{%user}}', 'id');

        $this->addForeignKey('fk_from_user_id', '{{%account_log}}', 'from_user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_to_user_id', '{{%account_log}}', 'to_user_id', '{{%user}}', 'id');
        $this->addForeignKey('fk_account_id', '{{%account_log}}', 'account_id', '{{%account}}', 'id');
    }
}
