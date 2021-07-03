<?php

use yii\db\Schema;
use yii\db\Migration;

class m161107_060126_fix_birthdate_in_user_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%user}}', 'birthdate', Schema::TYPE_DATETIME . " NOT NULL COMMENT 'Дата рождения'");
    }

    public function down()
    {
        $this->alterColumn('{{%user}}', 'birthdate', Schema::TYPE_TIMESTAMP . " NOT NULL COMMENT 'Дата рождения'");
    }
}
