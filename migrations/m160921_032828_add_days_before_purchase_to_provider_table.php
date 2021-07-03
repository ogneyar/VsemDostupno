<?php

use yii\db\Schema;
use yii\db\Migration;

class m160921_032828_add_days_before_purchase_to_provider_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%provider}}', 'days_before_purchase', Schema::TYPE_INTEGER . "(11) COMMENT 'Уведомлять до покупки'");
    }

    public function down()
    {
        $this->dropColumn('{{%provider}}', 'days_before_purchase');
    }
}
