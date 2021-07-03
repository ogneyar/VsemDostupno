<?php

use yii\db\Schema;
use yii\db\Migration;

class m151123_202819_add_slug_field_to_category_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%category}}', 'slug', Schema::TYPE_STRING . "(255) DEFAULT '' COMMENT 'Заголовок для URL'");
    }

    public function down()
    {
        $this->dropColumn('{{%category}}', 'slug');
    }
}
