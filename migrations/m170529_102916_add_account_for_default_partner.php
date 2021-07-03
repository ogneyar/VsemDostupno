<?php

use yii\db\Migration;
use app\models\Account;
use app\models\User;

class m170529_102916_add_account_for_default_partner extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus", "group", "subscription", "storage", "fraternity") NOT NULL COMMENT "Тип счета"');

        $defaultPartner = User::findOne(['email' => Yii::$app->params['defaultPartnerEmail']]);
        $types = [Account::TYPE_STORAGE, Account::TYPE_FRATERNITY];
        foreach ($types as $type) {
            $account = new Account(['user_id' => $defaultPartner->id, 'type' => $type, 'total' => 0]);
            $account->save();
        }
    }

    public function down()
    {
        $defaultPartner = User::findOne(['email' => Yii::$app->params['defaultPartnerEmail']]);
        $types = [Account::TYPE_STORAGE, Account::TYPE_FRATERNITY];
        foreach ($types as $type) {
            $account = Account::find()
                ->andWhere('user_id = :user_id', [':user_id' => $defaultPartner->id])
                ->andWhere('type = :type', [':type' => $type])
                ->one();
            $account->delete();
        }

        $this->alterColumn('{{%account}}', 'type', 'ENUM("deposit", "bonus", "group", "subscription") NOT NULL COMMENT "Тип счета"');
    }
}
