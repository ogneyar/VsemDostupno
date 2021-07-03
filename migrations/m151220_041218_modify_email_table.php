<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_041218_modify_email_table extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%email}}', 'type');

        $this->createIndex('idx_email_name', '{{%email}}', 'name');
    }

    public function down()
    {
        $this->addColumn('{{%email}}', 'type', "ENUM('text', 'html') NOT NULL COMMENT 'Тип письма'");

        $this->dropIndex('idx_email_name', '{{%email}}');
    }
}
