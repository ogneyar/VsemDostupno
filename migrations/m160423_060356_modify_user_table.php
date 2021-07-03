<?php

use yii\db\Schema;
use yii\db\Migration;

class m160423_060356_modify_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'birthdate', Schema::TYPE_TIMESTAMP . " NOT NULL COMMENT 'Дата рождения'");
        $this->addColumn('{{%user}}', 'citizen', Schema::TYPE_STRING . "(50) NOT NULL COMMENT 'Гражданство'");
        $this->addColumn('{{%user}}', 'birthplace', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Место рождения'");
        $this->addColumn('{{%user}}', 'registration', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Место регистрации'");
        $this->addColumn('{{%user}}', 'residence', Schema::TYPE_STRING . "(255) COMMENT 'Место фактического пребывания'");
        $this->addColumn('{{%user}}', 'passport', Schema::TYPE_STRING . "(10) NOT NULL COMMENT 'Серия и номер паспорта'");
        $this->addColumn('{{%user}}', 'passport_date', Schema::TYPE_TIMESTAMP . " NOT NULL COMMENT 'Дата выдачи паспорта'");
        $this->addColumn('{{%user}}', 'passport_department', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Кем выдан паспорт'");
        $this->addColumn('{{%user}}', 'itn', Schema::TYPE_STRING . "(12) COMMENT 'ИНН'");
        $this->addColumn('{{%user}}', 'skills', Schema::TYPE_TEXT . " COMMENT 'Профессиональные навыки'");
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'birthdate');
        $this->dropColumn('{{%user}}', 'citizen');
        $this->dropColumn('{{%user}}', 'birthplace');
        $this->dropColumn('{{%user}}', 'registration');
        $this->dropColumn('{{%user}}', 'residence');
        $this->dropColumn('{{%user}}', 'passport');
        $this->dropColumn('{{%user}}', 'passport_date');
        $this->dropColumn('{{%user}}', 'passport_department');
        $this->dropColumn('{{%user}}', 'itn');
        $this->dropColumn('{{%user}}', 'skills');
    }
}
