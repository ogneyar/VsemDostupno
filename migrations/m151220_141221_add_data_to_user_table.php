<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\City;
use app\models\User;
use app\models\Partner;
use app\models\Member;

class m151220_141221_add_data_to_user_table extends Migration
{
    public function up()
    {
        $users = [
            User::ROLE_ADMIN => new User([
                'role' => User::ROLE_ADMIN,
                'disabled' => 0,
                'email' => 'admin@vsemdostupno.ru',
                'password' => 'eimea4Ae',
                'phone' => '+79099190669',
                'firstname' => 'Олег',
                'lastname' => 'Прошунин',
                'patronymic' => 'Николаевич',
                'created_ip' => gethostbyname('localhost'),
            ]),
            User::ROLE_MEMBER => new User([
                'role' => User::ROLE_MEMBER,
                'disabled' => 0,
                'email' => 'member@vsemdostupno.ru',
                'password' => 'taefoSh3',
                'phone' => '+79099190669',
                'firstname' => 'Олег',
                'lastname' => 'Прошунин',
                'patronymic' => 'Николаевич',
                'created_ip' => gethostbyname('localhost'),
            ]),
            User::ROLE_PARTNER => new User([
                'role' => User::ROLE_PARTNER,
                'disabled' => 0,
                'email' => 'partner@vsemdostupno.ru',
                'password' => 'Uquah4Lu',
                'phone' => '+79099190669',
                'firstname' => 'Олег',
                'lastname' => 'Прошунин',
                'patronymic' => 'Николаевич',
                'created_ip' => gethostbyname('localhost'),
            ]),
        ];

        foreach ($users as $user) {
            $result = $user->save();
        }

        $city = City::findOne(['name' => 'Железнодорожный']);
        $partner = new Partner();
        $partner->name = 'ПО "Общее дело"';
        $partner->city_id = $city->id;
        $partner->user_id = $users[User::ROLE_PARTNER]->id;
        $partner->save();

        $member = new Member();
        $member->partner_id = $partner->id;
        $member->user_id = $users[User::ROLE_MEMBER]->id;
        $member->save();
    }

    public function down()
    {
        $this->delete('{{%member}}');
        $this->execute('ALTER TABLE {{%member}} AUTO_INCREMENT = 1');

        $this->delete('{{%partner}}');
        $this->execute('ALTER TABLE {{%partner}} AUTO_INCREMENT = 1');

        $this->delete('{{%user}}');
        $this->execute('ALTER TABLE {{%user}} AUTO_INCREMENT = 1');
    }
}
