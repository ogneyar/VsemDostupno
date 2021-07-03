<?php

use yii\db\Migration;
use app\models\OrderStatus;

class m160921_040629_add_data_order_status_table extends Migration
{
    public function up()
    {
        $status = [
            OrderStatus::STATUS_NEW => 'Новый',
            OrderStatus::STATUS_CANCELED => 'Отмененый',
            OrderStatus::STATUS_VERIFIED => 'Проверенный',
            OrderStatus::STATUS_COMPLETED => 'Завершенный',
        ];

        foreach ($status as $type => $name) {
            $orderStatus = new OrderStatus([
                'type' => $type,
                'name' => $name,
            ]);
            $orderStatus->save();
        }
    }

    public function down()
    {
        $this->delete('{{%order_status}}');
        $this->execute('ALTER TABLE {{%order_status}} AUTO_INCREMENT = 1');
    }
}
