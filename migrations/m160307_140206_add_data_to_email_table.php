<?php

use yii\db\Migration;
use app\models\Email;

class m160307_140206_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'order-partner',
                'subject' => 'Заказ №{{%id}}',
                'body' => '<p>Оформлен заказ на сайте.</p><p>{{%information}}</p>',
            ]),
            new Email([
                'name' => 'order-customer',
                'subject' => 'Заказ №{{%id}}',
                'body' => '<p>Спасибо за Ваш заказ на сайте!</p><p>{{%information}}</p>',
            ]),
        ];

        foreach ($emails as $email) {
            $email->body .= '<p>--<br>С уважением,<br>' .
                'Прошунин Олег Николаевич' .
                '<br>Тел.: +7 (905) 570-73-74, +7 (909) 919-06-69</p>';

            $email->save();
        }
    }

    public function down()
    {
        $names = [
            'order-partner',
            'order-customer',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
