<?php

use yii\db\Migration;
use app\models\User;
use app\models\Account;

/**
 * Handles adding accounts to table `users`.
 */
class m160615_131835_add_accounts_to_users extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        foreach (User::find()->each() as $user) {
            if ($user->role != User::ROLE_ADMIN) {
                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS];
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    $account->save();
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->delete('{{%account}}');
        $this->execute('ALTER TABLE {{%account}} AUTO_INCREMENT = 1');
    }
}
