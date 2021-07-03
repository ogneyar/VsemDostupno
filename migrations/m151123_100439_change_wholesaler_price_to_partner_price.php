<?php

use yii\db\Schema;
use yii\db\Migration;

class m151123_100439_change_wholesaler_price_to_partner_price extends Migration
{
    public function up()
    {
        $this->execute('ALTER TABLE {{%product}} CHANGE wholesaler_price partner_price decimal(19,2)');
        $this->alterColumn('{{%product}}', 'partner_price', Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Цена для партнеров'");
    }

    public function down()
    {
        $this->execute('ALTER TABLE {{%product}} CHANGE partner_price wholesaler_price decimal(19,2)');
        $this->alterColumn('{{%product}}', 'wholesaler_price', Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Цена для оптовиков'");
    }
}
