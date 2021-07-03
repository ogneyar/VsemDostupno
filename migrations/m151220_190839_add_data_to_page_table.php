<?php

use yii\db\Schema;
use yii\db\Migration;

class m151220_190839_add_data_to_page_table extends Migration
{
    public function up()
    {
        $this->BatchInsert('{{%page}}', ['slug', 'visibility', 'title', 'content'], [
            ['profile-register-finish', 0, 'Поздравляем с успешной регистрацией!', '<p>На ваш почтовый адрес было отправлено письмо для активации аккаунта.</p><p>Чтобы активировать аккаунт перейдите по ссылке в письме.</p>'],
            ['profile-register-success', 0, 'Ваш аккаунт успешно активирован!', '<p>Можно перейти на <a href="/">главную страницу</a>, чтобы совершить покупки.</p>'],
            ['profile-register-fail', 0, 'Произошла ошибка!', '<p>Возможно, аккаут уже был активирован.</p><p>Попробуйте перейти в <a href="/profile">личный кабинет</a>.</p>'],
            ['profile-forgot-finish', 0, 'Восстановление пароля', '<p>На ваш почтовый адрес было отправлено письмо для восстановления пароля.</p><p>Чтобы восстановить пароль перейдите по ссылке в письме.</p>'],
            ['profile-forgot-success', 0, 'Пароль успешно восстановлен!', '<p>Можно перейти на <a href="/">главную страницу</a>, чтобы совершить покупки.</p>'],
            ['profile-forgot-fail', 0, 'Произошла ошибка', '<p>Возможно, уже был переход по устаревшей ссылке.</p><p>Попробуйте перейти в <a href="/profile">личный кабинет</a>.</p>'],
        ]);
    }

    public function down()
    {
        $slugs = [
            'profile-register-finish',
            'profile-register-success',
            'profile-register-fail',
            'profile-forgot-finish',
            'profile-forgot-success',
            'profile-forgot-fail',
        ];

        $this->delete('{{%page}}', ['slug' => $slugs]);
    }
}
