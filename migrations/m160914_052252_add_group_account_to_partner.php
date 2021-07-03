<?php

use yii\db\Migration;
use app\models\Partner;
use app\models\Account;

class m160914_052252_add_group_account_to_partner extends Migration
{
    public function up()
    {
        foreach (Partner::find()->each() as $partner) {
            $account = new Account(['user_id' => $partner->user->id, 'type' => Account::TYPE_GROUP, 'total' => 0]);
            $account->save();
        }
    }

    public function down()
    {
        foreach (Partner::find()->each() as $partner) {
            $partner->group->delete();
        }
    }
}
