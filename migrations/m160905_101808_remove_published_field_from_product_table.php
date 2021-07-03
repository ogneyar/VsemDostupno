<?php

use yii\db\Schema;
use yii\db\Migration;

class m160905_101808_remove_published_field_from_product_table extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%product}}', 'published');
    }

    public function down()
    {
        $this->addColumn('{{%product}}', 'published', Schema::TYPE_BOOLEAN . '(1) DEFAULT 0 COMMENT "Опубликованный"');
        $this->execute('UPDATE {{%product}} SET published = 1');
    }
}
