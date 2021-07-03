<?php

use yii\db\Migration;

class m161204_132003_modify_number_field_of_user_table extends Migration
{
    public function up()
    {
        $this->dropIndex('unique_user_number_key', '{{%user}}');
    }

    public function down()
    {
        $this->createIndex('unique_user_number_key', '{{%user}}', 'number', true);
    }
}
