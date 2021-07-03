<?php

use yii\db\Migration;
use app\models\Parameter;

class m161014_174312_add_data_to_parameter_table extends Migration
{
    public function up()
    {
        $parameters = [
            new Parameter([
                'name' => 'subscriber-payment',
                'value' => '20',
            ]),
        ];

        foreach ($parameters as $parameter) {
            $parameter->save();
        }
    }

    public function down()
    {
        $names = [
            'subscriber-payment',
        ];

        $this->delete('{{%parameter}}', ['name' => $names]);
    }
}
