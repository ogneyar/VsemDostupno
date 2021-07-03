<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\OrderStatus;

class m160921_114626_add_status_to_order_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%order}}', 'order_status_id', Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор статуса'");

        $orderStatus = OrderStatus::findOne(['type' => OrderStatus::STATUS_COMPLETED]);
        $this->execute('UPDATE {{%order}} SET order_status_id = :order_status_id', [':order_status_id' => $orderStatus->id]);

        $this->addForeignKey('fk_order_order_status_id', '{{%order}}', 'order_status_id', '{{%order_status}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_order_order_status_id', '{{%order}}');

        $this->dropColumn('{{%order}}', 'order_status_id');
    }
}
