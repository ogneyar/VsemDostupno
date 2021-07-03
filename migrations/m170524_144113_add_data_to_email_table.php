<?php

use yii\db\Migration;
use app\models\Email;

class m170524_144113_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'notify-product-min-inventory',
                'subject' => 'Заканчиваются товары на складе',
                'body' => '<p>Список товаров:{{%list}}</p>',
            ]),
            new Email([
                'name' => 'notify-product-expiry',
                'subject' => 'Заканчивается срок годности товаров',
                'body' => '<p>Список товаров:{{%list}}</p>',
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
            'notify-product-min-inventory',
            'notify-product-expiry',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
