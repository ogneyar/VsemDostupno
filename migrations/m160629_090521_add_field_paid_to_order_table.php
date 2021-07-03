<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles adding paid to table `order`.
 */
class m160629_090521_add_field_paid_to_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%order}}', 'paid_total', Schema::TYPE_MONEY . "(19,2) NOT NULL DEFAULT 0 COMMENT 'Оплаченная стоимость'");
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%order}}', 'paid_total');
    }
}
