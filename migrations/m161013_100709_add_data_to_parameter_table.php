<?php

use yii\db\Migration;
use app\models\Parameter;

class m161013_100709_add_data_to_parameter_table extends Migration
{
    public function up()
    {
        $parameters = [
            new Parameter([
                'name' => 'recommender-percents',
                'value' => '3',
            ]),
        ];

        foreach ($parameters as $parameter) {
            $parameter->save();
        }
    }

    public function down()
    {
        $names = [
            'recommender-percents',
        ];

        $this->delete('{{%parameter}}', ['name' => $names]);
    }
}
