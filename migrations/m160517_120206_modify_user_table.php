<?php

use yii\db\Schema;
use yii\db\Migration;

class m160517_120206_modify_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'recommender', Schema::TYPE_STRING . "(255) COMMENT 'Рекомендатель'");
        $this->addColumn('{{%user}}', 'number', Schema::TYPE_INTEGER . "(11) COMMENT 'Номер'");
        $this->createIndex('unique_user_number_key', '{{%user}}', 'number', true);
    }

    public function down()
    {
        $this->dropIndex('unique_user_number_key', '{{%user}}');
        $this->dropColumn('{{%user}}', 'recommender');
        $this->dropColumn('{{%user}}', 'number');
    }
}
