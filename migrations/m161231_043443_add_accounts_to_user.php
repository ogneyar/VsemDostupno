<?php

use yii\db\Migration;
use app\models\Account;
use app\models\User;

class m161231_043443_add_accounts_to_user extends Migration
{
    public function up()
    {
        $query = User::find()
            ->where(['IN', 'role', [User::ROLE_MEMBER, User::ROLE_PARTNER, User::ROLE_PROVIDER]]);

        foreach ($query->each() as $user) {
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
