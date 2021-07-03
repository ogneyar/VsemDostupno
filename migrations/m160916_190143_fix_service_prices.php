<?php

use yii\db\Migration;
use app\models\Service;

class m160916_190143_fix_service_prices extends Migration
{
    public function up()
    {
        $this->execute('UPDATE {{%service}} SET member_price = price WHERE price > 0');
    }

    public function down()
    {
        $this->execute('UPDATE {{%service}} SET member_price = NULL');
    }
}
