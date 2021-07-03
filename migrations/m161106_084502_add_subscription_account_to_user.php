<?php

use yii\db\Migration;
use app\models\Account;
use app\models\User;

class m161106_084502_add_subscription_account_to_user extends Migration
{
    public function up()
    {
        $query = User::find()
            ->where(['IN', 'role', [User::ROLE_MEMBER, User::ROLE_PARTNER, User::ROLE_PROVIDER]]);

        foreach ($query->each() as $user) {
            $account = new Account(['user_id' => $user->id, 'type' => Account::TYPE_SUBSCRIPTION, 'total' => 0]);
            $account->save();
        }
    }

    public function down()
    {
        $query = User::find()
            ->where(['IN', 'role', [User::ROLE_MEMBER, User::ROLE_PARTNER, User::ROLE_PROVIDER]]);

        foreach ($query->each() as $user) {
            $user->subscription->delete();
        }
    }
}
