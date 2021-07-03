<?php

use yii\db\Migration;
use app\models\Page;

class m160306_000035_add_data_to_page_table extends Migration
{
    public function up()
    {
        $pages = [
            new Page([
                'slug' => 'cart-checkout-success',
                'visibility' => 0,
                'title' => 'Заказ успешно оформлен!',
                'content' => '<p>Запомните номер Вашего заказа: <b>{{%id}}</b>.</p><p>Сумма заказа: <b>{{%formattedTotal}}</b></p><p>Можно перейти на <a href="/">главную страницу</a>, чтобы совершить покупки.</p>',
            ]),
            new Page([
                'slug' => 'cart-checkout-fail',
                'visibility' => 0,
                'title' => 'Ошибка при офомлении заказа!',
                'content' => '<p>Свяжитесь с администрацией сайта, чтобы устранить причину сбоя.</p>',
            ]),
        ];

        foreach ($pages as $page) {
            $page->save();
        }
    }

    public function down()
    {
        $slugs = [
            'cart-checkout-success',
            'cart-checkout-fail',
        ];

        $this->delete('{{%page}}', ['slug' => $slugs]);
    }
}
