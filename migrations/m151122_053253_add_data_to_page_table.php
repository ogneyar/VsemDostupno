<?php

use yii\db\Schema;
use yii\db\Migration;

class m151122_053253_add_data_to_page_table extends Migration
{
    public function up()
    {
        $this->BatchInsert('{{%page}}', ['slug', 'title', 'content'], [
            ['pomoshch', 'Помощь', '<p>Страница в разработке.</>'],
            ['o-nas', 'О нас', '<p>Страница в разработке.</>'],
            ['kontakty', 'Контакты', '<p>Страница в разработке.</>'],
            ['oplata', 'Оплата', '<p>Страница в разработке.</>'],
            ['dostavka', 'Доставка', '<p>Страница в разработке.</>'],
            ['punkty-vydachi', 'Пункты выдачи', '<p>Страница в разработке.</>'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%page}}');
        $this->execute('ALTER TABLE {{%page}} AUTO_INCREMENT = 1');
    }
}
