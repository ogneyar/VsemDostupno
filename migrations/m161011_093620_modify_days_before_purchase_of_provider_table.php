<?php

use yii\db\Schema;
use yii\db\Migration;

class m161011_093620_modify_days_before_purchase_of_provider_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%provider}}', 'days_before_purchase', Schema::TYPE_INTEGER . "(11) COMMENT 'Уведомлять до закупки'");
    }

    public function down()
    {
        $this->alterColumn('{{%provider}}', 'days_before_purchase', Schema::TYPE_INTEGER . "(11) COMMENT 'Уведомлять до покупки'");
    }
}
