<?php

use yii\db\Migration;
use app\models\Parameter;

class m161019_121216_remove_data_from_parameter_table extends Migration
{
    public function up()
    {
        $names = [
            'recommender-percents',
        ];

        $this->delete('{{%parameter}}', ['name' => $names]);
    }

    public function down()
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
}
