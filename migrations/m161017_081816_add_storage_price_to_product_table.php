<?php

use yii\db\Schema;
use yii\db\Migration;

class m161017_081816_add_storage_price_to_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'storage_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Складской сбор"');
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'storage_price');
    }
}
