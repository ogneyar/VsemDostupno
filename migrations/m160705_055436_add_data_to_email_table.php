<?php

use yii\db\Migration;
use app\models\Email;

/**
 * Handles adding data to table `email`.
 */
class m160705_055436_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'confirm-transfer',
                'subject' => 'Подтверждение перевода на сайте ',
                'body' => '<p>Чтобы подтвердить перевод перейдите по <a href="{{%url}}">ссылке</a>.</p><p>Если считаете, что данное письмо адресовано не Вам, то просто игнорируйте его.</p><p>Для перехода по ссылке требуется авторизация на сайте.</p>',
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
            'confirm-transfer',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
