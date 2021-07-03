<?php

use yii\db\Migration;
use app\models\Email;

/**
 * Handles adding data to table `email_table`.
 */
class m160721_132128_add_data_to_email_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $emails = [
            new Email([
                'name' => 'account-log',
                'subject' => 'Действие со счетом',
                'body' => '<p><b>Счет:</b> {{%typeName}}</p><p><b>Действие:</b> {{%message}}</p><p><b>Сумма:</b> {{%amount}}</p><p><b>Остаток:</b> {{%total}}</p>',
            ]),
        ];

        foreach ($emails as $email) {
            $email->body .= '<p>--<br>С уважением,<br>Администрация сайта.';
            $email->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $names = [
            'account-log',
        ];

        $this->delete('{{%email}}', ['name' => $names]);
    }
}
