<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Email;

class m151220_042749_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'register',
                'subject' => 'Активация аккаута',
                'body' => '<p>Спасибо за регистрацию!</p><p>Чтобы активировать аккаунт перейдите по <a href="{{%url}}">ссылке</a>.</p><p>Если считаете, что данное письмо адресовано не Вам, то просто игнорируйте его.</p>',
            ]),
            new Email([
                'name' => 'forgot',
                'subject' => 'Восстановление пароля',
                'body' => '<p>Чтобы восстановить пароль перейдите по <a href="{{%url}}">ссылке</a>.</p><p>Если считаете, что данное письмо адресовано не Вам, то просто игнорируйте его.</p>',
            ]),
        ];

        foreach ($emails as $email) {
            $email->body .= '<p>--<br>С уважением,<br>' .
                'руководитель проекта Прошунин Олег Николаевич' .
                '<br>Тел.: +7 (905) 570-73-74, +7 (909) 919-06-69</p>';

            $email->save();
        }
    }

    public function down()
    {
        $this->delete('{{%email}}');
        $this->execute('ALTER TABLE {{%email}} AUTO_INCREMENT = 1');
    }
}
