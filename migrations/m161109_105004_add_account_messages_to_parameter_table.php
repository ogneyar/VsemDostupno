<?php

use yii\db\Migration;
use app\models\Parameter;

class m161109_105004_add_account_messages_to_parameter_table extends Migration
{
    public function up()
    {
        $parameter = Parameter::findOne(['name' => 'account-messages']);
        $parameter->value .= ';Возврат паевого взноса';
        $parameter->save();
    }

    public function down()
    {
        $parameter = Parameter::findOne(['name' => 'account-messages']);
        $parameter->value = preg_replace('/Возврат паевого взноса/', '', $parameter->value);
        $parameter->value = preg_replace('/;+/', ';', $parameter->value);
        $parameter->value = preg_replace('/;$/', '', $parameter->value);
        $parameter->save();
    }
}
