<?php

use yii\db\Migration;

class m160906_055435_fix_order_has_product_table extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}');
        $this->addForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}', 'product_id', '{{%product}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}');
        $this->addForeignKey('fk_order_has_product_product_id', '{{%order_has_product}}', 'product_id', '{{%product}}', 'id');
    }
}
