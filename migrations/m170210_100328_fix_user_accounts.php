<?php

use yii\db\Migration;
use app\models\User;
use app\models\Account;

class m170210_100328_fix_user_accounts extends Migration
{
    public function up()
    {
        foreach (User::find()->where('role != :role', [':role' => User::ROLE_ADMIN])->each() as $user) {
            if (!$user->deposit) {
                $account = new Account(['user_id' => $user->id, 'type' => Account::TYPE_DEPOSIT, 'total' => 0]);
                $account->save();
            }
            if (!$user->bonus) {
                $account = new Account(['user_id' => $user->id, 'type' => Account::TYPE_BONUS, 'total' => 0]);
                $account->save();
            }
            if (!$user->subscription) {
                $account = new Account(['user_id' => $user->id, 'type' => Account::TYPE_SUBSCRIPTION, 'total' => 0]);
                $account->save();
            }
            if ($user->role == User::ROLE_PARTNER && !$user->group) {
                $account = new Account(['user_id' => $user->id, 'type' => Account::TYPE_GROUP, 'total' => 0]);
                $account->save();
            }
        }
    }

    public function down()
    {
    }
}
