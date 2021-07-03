<?php

use yii\db\Migration;
use app\models\Email;

class m161106_093326_add_data_to_email_table extends Migration
{
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'notify-modified-product',
                'subject' => 'Изменение товара "{{%name}}"',
                'body' => '<p>Ссылка для просмотра на сайте: <a href="{{%viewUrl}}" target="_blank">{{%name}}</a></p>' .
                        '<p>Ссылка для редактирования: <a href="{{%updateUrl}}" target="_blank">{{%name}}</a></p>',
            ]),
            new Email([
                'name' => 'notify-modified-service',
                'subject' => 'Изменение услуги "{{%name}}"',
                'body' => '<p>Ссылка для просмотра на сайте: <a href="{{%viewUrl}}" target="_blank">{{%name}}</a></p>' .
                        '<p>Ссылка для редактирования: <a href="{{%updateUrl}}" target="_blank">{{%name}}</a></p>',
            ]),
            new Email([
                'name' => 'notify-registered-new-user',
                'subject' => 'Зарегистрирован новый пользователь "{{%name}}"',
                'body' => '<p>Ссылка для просмотра: <a href="{{%viewUrl}}" target="_blank">{{%name}}</a></p>' .
                        '<p>Ссылка для редактирования: <a href="{{%updateUrl}}" target="_blank">{{%name}}</a></p>',
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
            'notify-modified-product',
            'notify-modified-service',
            'notify-registered-new-user',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
