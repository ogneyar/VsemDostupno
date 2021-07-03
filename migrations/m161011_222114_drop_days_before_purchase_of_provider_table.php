<?php

use yii\db\Schema;
use yii\db\Migration;

class m161011_222114_drop_days_before_purchase_of_provider_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('{{%provider}}', 'days_before_purchase');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->addColumn('{{%provider}}', 'days_before_purchase', Schema::TYPE_INTEGER . "(11) COMMENT 'Уведомлять до закупки'");
    }
}
