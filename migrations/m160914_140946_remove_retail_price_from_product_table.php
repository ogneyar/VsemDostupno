<?php

use yii\db\Schema;
use yii\db\Migration;

class m160914_140946_remove_retail_price_from_product_table extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%product}}', 'retail_price');
    }

    public function down()
    {
        $this->addColumn('{{%product}}', 'retail_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Розничная цена"');
    }
}
