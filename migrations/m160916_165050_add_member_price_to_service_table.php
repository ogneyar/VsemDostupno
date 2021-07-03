<?php

use yii\db\Schema;
use yii\db\Migration;

class m160916_165050_add_member_price_to_service_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%service}}', 'price', Schema::TYPE_MONEY . '(19,2) COMMENT "Цена для всех"');
        $this->addColumn('{{%service}}', 'member_price', Schema::TYPE_MONEY . '(19,2) COMMENT "Цена для участников"');
    }

    public function down()
    {
        $this->alterColumn('{{%service}}', 'price', Schema::TYPE_MONEY . '(19,2) COMMENT "Цена"');
        $this->dropColumn('{{%service}}', 'member_price');
    }
}
