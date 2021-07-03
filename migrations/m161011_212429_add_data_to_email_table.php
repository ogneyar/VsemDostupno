<?php

use yii\db\Migration;
use app\models\Email;

class m161011_212429_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'update-category-dates',
                'subject' => 'Обновление дат заказов и закупок для категорий {{%date}}',
                'body' => '<p>Список обновлений:</p><p>{{%list}}</p>',
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
            'update-category-dates',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
