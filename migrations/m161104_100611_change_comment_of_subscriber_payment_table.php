<?php

use yii\db\Migration;

class m161104_100611_change_comment_of_subscriber_payment_table extends Migration
{
    public function up()
    {
        $this->execute('ALTER TABLE {{%subscriber_payment}} COMMENT = "Членский взнос"');
    }

    public function down()
    {
        $this->execute('ALTER TABLE {{%subscriber_payment}} COMMENT = "Абонентская плата"');
    }
}
