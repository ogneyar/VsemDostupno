<?php

use yii\db\Schema;
use yii\db\Migration;

class m160914_042222_add_contacts_field_to_service_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%service}}', 'contacts', Schema::TYPE_STRING . '(255) COMMENT "Контакты"');
    }

    public function down()
    {
        $this->dropColumn('{{%service}}', 'contacts');
    }
}
