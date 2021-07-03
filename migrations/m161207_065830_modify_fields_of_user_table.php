<?php

use yii\db\Schema;
use yii\db\Migration;

class m161207_065830_modify_fields_of_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'ext_phones', Schema::TYPE_STRING . '(255) COMMENT "Дополнительные телефоны"');
        $this->alterColumn('{{%user}}', 'itn', Schema::TYPE_STRING . '(30) COMMENT "ИНН"');
        $this->alterColumn('{{%user}}', 'passport', Schema::TYPE_STRING . '(30) NOT NULL COMMENT "Серия и номер паспорта"');
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'ext_phones');
        $this->alterColumn('{{%user}}', 'itn', Schema::TYPE_STRING . '(12) COMMENT "ИНН"');
        $this->alterColumn('{{%user}}', 'passport', Schema::TYPE_STRING . '(10) NOT NULL COMMENT "Серия и номер паспорта"');
    }
}
