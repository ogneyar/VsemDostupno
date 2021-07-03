<?php

use yii\db\Schema;
use yii\db\Migration;

class m160131_072908_modify_category_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%category}}', 'visibility', Schema::TYPE_BOOLEAN . "(1) DEFAULT 1 COMMENT 'Видимость'");
    }

    public function down()
    {
        $this->dropColumn('{{%category}}', 'visibility');
    }
}
