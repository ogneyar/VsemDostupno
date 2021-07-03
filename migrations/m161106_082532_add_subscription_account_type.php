<?php

use yii\db\Schema;
use yii\db\Migration;

class m161106_082532_add_subscription_account_type extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus", "group", "subscription") NOT NULL COMMENT "Тип счета"');
    }

    public function down()
    {
        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus", "group") NOT NULL COMMENT "Тип счета"');
    }
}
