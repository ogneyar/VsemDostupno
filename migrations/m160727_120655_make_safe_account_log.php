<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\AccountLog;

class m160727_120655_make_safe_account_log extends Migration
{
    public function up()
    {
        $this->addColumn('{{%account_log}}', 'from_firstname', Schema::TYPE_STRING . '(255) COMMENT "Имя отправителя"');
        $this->addColumn('{{%account_log}}', 'from_lastname', Schema::TYPE_STRING . '(255) COMMENT "Фамилия отправителя"');
        $this->addColumn('{{%account_log}}', 'from_patronymic', Schema::TYPE_STRING . '(255) COMMENT "Отчество отправителя"');
        $this->addColumn('{{%account_log}}', 'to_firstname', Schema::TYPE_STRING . '(255) COMMENT "Имя получателя"');
        $this->addColumn('{{%account_log}}', 'to_lastname', Schema::TYPE_STRING . '(255) COMMENT "Фамилия получателя"');
        $this->addColumn('{{%account_log}}', 'to_patronymic', Schema::TYPE_STRING . '(255) COMMENT "Отчество получателя"');

        foreach (AccountLog::find()->each() as $log) {
            if ($log->fromUser) {
                $log->from_firstname = $log->fromUser->firstname;
                $log->from_lastname = $log->fromUser->lastname;
                $log->from_patronymic = $log->fromUser->patronymic;
            }
            if ($log->toUser) {
                $log->to_firstname = $log->toUser->firstname;
                $log->to_lastname = $log->toUser->lastname;
                $log->to_patronymic = $log->toUser->patronymic;
            }
            $log->save();
        }
    }

    public function down()
    {
        $this->dropColumn('{{%account_log}}', 'from_firstname');
        $this->dropColumn('{{%account_log}}', 'from_lastname');
        $this->dropColumn('{{%account_log}}', 'from_patronymic');
        $this->dropColumn('{{%account_log}}', 'to_firstname');
        $this->dropColumn('{{%account_log}}', 'to_lastname');
        $this->dropColumn('{{%account_log}}', 'to_patronymic');
    }
}
