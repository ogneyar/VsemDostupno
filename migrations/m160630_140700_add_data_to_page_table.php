<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles adding data to table `page`.
 */
class m160630_140700_add_data_to_page_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->BatchInsert('{{%page}}', ['slug', 'visibility', 'title', 'content'], [
            ['profile-account-swap-fail', 0, 'Произошла ошибка!', '<p>Обратитесь к администратору сайта.</p>'],
            ['profile-account-transfer-fail', 0, 'Произошла ошибка!', '<p>Обратитесь к администратору сайта.</p>'],
            ['profile-account-transfer-success', 0, 'Перевод пользователю сайта', '<p>На ваш почтовый адрес было отправлено письмо для подтверждения перевода.</p><p>Чтобы подтвердить перевод, перейдите по ссылке в письме.</p>'],
            ['profile-account-transfer-finish', 0, 'Перевод пользователю сайта', 'Перевод успешно подтвержден.'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $slugs = [
            'profile-account-swap-fail',
            'profile-account-transfer-fail',
            'profile-account-transfer-success',
            'profile-account-transfer-finish',
        ];

        $this->delete('{{%page}}', ['slug' => $slugs]);
    }
}
