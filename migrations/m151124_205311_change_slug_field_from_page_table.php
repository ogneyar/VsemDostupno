<?php

use yii\db\Schema;
use yii\db\Migration;

class m151124_205311_change_slug_field_from_page_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%page}}', 'slug', Schema::TYPE_STRING . "(255) DEFAULT '' COMMENT 'Заголовок для URL'");
    }

    public function down()
    {
        $this->alterColumn('{{%page}}', 'slug', Schema::TYPE_STRING . "(255) COMMENT 'Заголовок для URL'");
    }
}
