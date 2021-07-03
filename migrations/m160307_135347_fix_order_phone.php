<?php

use yii\db\Migration;
use app\models\Order;

class m160307_135347_fix_order_phone extends Migration
{
    public function up()
    {
        foreach (Order::find()->each() as $order) {
            $order->phone = '+' . preg_replace('/\D+/', '', $order->phone);
            $order->save();
        }
    }

    public function down()
    {
    }
}
