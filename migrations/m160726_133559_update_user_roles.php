<?php

use yii\db\Schema;
use yii\db\Migration;

class m160726_133559_update_user_roles extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%user}}', 'role', "ENUM('admin', 'member', 'partner', 'provider') NOT NULL COMMENT 'Роль'");
    }

    public function down()
    {
        $this->alterColumn('{{%user}}', 'role', "ENUM('admin', 'member', 'partner') NOT NULL COMMENT 'Роль'");
    }
}
