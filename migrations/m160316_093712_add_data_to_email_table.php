<?php

use yii\db\Migration;
use app\models\Email;

class m160316_093712_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'active-profile',
                'subject' => 'Личный кабинет на сайте ',
                'body' => '<p>{{%firstname}} {{%patronymic}}, Ваш личный кабинет на сайте <a href="{{%url}}">{{%url}}</a> активен. Благодарим Вас за проявленный интерес к нам. Желаем Вам приятных покупок.</p>',
            ]),
        ];

        foreach ($emails as $email) {
            $email->body .= '<p>--<br>С уважением,<br>' .
                'Администрация сайта.';

            $email->save();
        }
    }

    public function down()
    {
        $names = [
            'active-profile',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
