<?php

use yii\db\Migration;
use app\models\Email;

class m161012_123541_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'notify-purchase',
                'subject' => 'Заказ товаров {{%date}}',
                'body' => '<p>Список товаров:</p><p>{{%list}}</p>',
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
            'notify-purchase',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
