<?php

use yii\db\Migration;
use app\models\Parameter;

/**
 * Handles adding messages to table `parameter` tables.
 */
class m160727_125230_add_messages_to_parameter_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $parameter = new Parameter([
            'name' => 'account-messages',
            'value' => 'Добровольный взнос;Членский взнос;Паевой взнос',
        ]);
        $parameter->save();
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $parameter = Parameter::findOne(['name' => 'account-messages']);
        if ($parameter) {
            $parameter->delete();
        }
    }
}
