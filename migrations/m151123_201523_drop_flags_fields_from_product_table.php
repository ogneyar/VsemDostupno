<?php

use yii\db\Schema;
use yii\db\Migration;

class m151123_201523_drop_flags_fields_from_product_table extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%product}}', 'featured');
        $this->dropColumn('{{%product}}', 'recent');
        $this->dropColumn('{{%product}}', 'purchase');
    }

    public function down()
    {
        $this->addColumn('{{%product}}', 'featured', Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Спецпредложение'");
        $this->addColumn('{{%product}}', 'recent', Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Новый'");
        $this->addColumn('{{%product}}', 'purchase', Schema::TYPE_BOOLEAN . "(1) DEFAULT 0 COMMENT 'Закупка'");
    }
}
