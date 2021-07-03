<?php

use yii\db\Schema;
use yii\db\Migration;

class m160914_104725_add_fields_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'published', Schema::TYPE_BOOLEAN . '(1) DEFAULT 0 COMMENT "Опубликованный"');
        $this->execute('UPDATE {{%product}} SET published = 1');

        $this->addColumn('{{%product}}', 'purchase_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Закупочная цена"');
        $this->addColumn('{{%product}}', 'retail_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Розничная цена"');
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'published');
        $this->dropColumn('{{%product}}', 'purchase_price');
        $this->dropColumn('{{%product}}', 'retail_price');
    }
}
