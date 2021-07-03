<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\User;

class m160512_094102_modify_user_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%user}}', 'registration', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Адрес регистрации'");
        $this->alterColumn('{{%user}}', 'residence', Schema::TYPE_STRING . "(255) COMMENT 'Адрес фактического пребывания'");

        $this->renameColumn('{{%user}}', 'birthplace', 'birth_area');
        $this->alterColumn('{{%user}}', 'birth_area', Schema::TYPE_STRING . "(255) COMMENT 'Область рождения'");

        $this->addColumn('{{%user}}', 'birth_zone', Schema::TYPE_STRING . "(255) COMMENT 'Район рождения'");
        $this->addColumn('{{%user}}', 'birth_city', Schema::TYPE_STRING . "(255) COMMENT 'Город рождения'");
        $this->addColumn('{{%user}}', 'birth_locality', Schema::TYPE_STRING . "(255) COMMENT 'Населенный пункт рождения'");
    }

    public function down()
    {
        $this->alterColumn('{{%user}}', 'registration', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Место регистрации'");
        $this->alterColumn('{{%user}}', 'residence', Schema::TYPE_STRING . "(255) COMMENT 'Место фактического пребывания'");

        foreach (User::find()->each() as $user) {
            $user->birth_area = implode(', ', array_filter([
                $user->birth_area,
                $user->birth_zone,
                $user->birth_city,
                $user->birth_locality,
            ]));
            $user->save();
        }

        $this->renameColumn('{{%user}}', 'birth_area', 'birthplace');
        $this->alterColumn('{{%user}}', 'birthplace', Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Место рождения'");

        $this->dropColumn('{{%user}}', 'birth_zone');
        $this->dropColumn('{{%user}}', 'birth_city');
        $this->dropColumn('{{%user}}', 'birth_locality');
    }
}
