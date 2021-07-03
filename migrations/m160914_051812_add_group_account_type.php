<?php

use yii\db\Schema;
use yii\db\Migration;

class m160914_051812_add_group_account_type extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus", "group") NOT NULL COMMENT "Тип счета"');
    }

    public function down()
    {
        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus") NOT NULL COMMENT "Тип счета"');
    }
}
