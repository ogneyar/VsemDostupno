<?php

use yii\db\Schema;
use yii\db\Migration;

class m151122_185909_add_prices_to_product_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%product}}', 'price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Цена для всех"');

        $this->addColumn('{{%product}}', 'member_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Цена для участников"');
        $this->addColumn('{{%product}}', 'wholesaler_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Цена для оптовиков"');
    }

    public function down()
    {
        $this->alterColumn('{{%product}}', 'price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Цена"');

        $this->dropColumn('{{%product}}', 'member_price');
        $this->dropColumn('{{%product}}', 'wholesaler_price');
    }
}
