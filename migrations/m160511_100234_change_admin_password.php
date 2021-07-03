<?php

use yii\db\Migration;
use app\models\User;

class m160511_100234_change_admin_password extends Migration
{
    public function up()
    {
        $this->changeAdminPassword('AhM4aete');
    }

    public function down()
    {
        $this->changeAdminPassword('eimea4Ae');
    }

    private function changeAdminPassword($password)
    {
        $user = User::find(['email' => 'admin@vsemdostupno.ru'])->one();

        if ($user) {
            $user->password = $password;
            $user->save();
        }
    }
}
