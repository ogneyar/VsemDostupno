<?php

use yii\db\Schema;
use yii\db\Migration;

class m160508_125237_modify_foreign_key_order_table extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_order_user_id', '{{%order}}');
        $this->addForeignKey('fk_order_user_id', '{{%order}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_order_user_id', '{{%order}}');
        $this->addForeignKey('fk_order_user_id', '{{%order}}', 'user_id', '{{%user}}', 'id');
    }
}
