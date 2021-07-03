<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_062501_add_data_to_city_table extends Migration
{
    public function up()
    {
        $this->BatchInsert('{{%city}}', ['name'], [
            ['Железнодорожный'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%city}}');
        $this->execute('ALTER TABLE {{%city}} AUTO_INCREMENT = 1');
    }
}
