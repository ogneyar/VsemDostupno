<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Category;

class m151114_013435_add_data_to_category_table extends Migration
{
    public function up()
    {
        $categories = [
            'Мясные продукты' => ['Свежемороженое мясо', 'Полуфабрикаты', 'Колбасы'],
            'Молочные продукты' => [],
            'Овощи' => [],
            'Фрукты' => [],
            'Разносолы' => [],
            'Мед' => [],
            'Консервы' => ['Тушеное мясо', 'Варенье'],
        ];

        foreach ($categories as $name => $subCategories) {
            $root = new Category;
            $root->name = $name;
            $root->saveNode();

            foreach ($subCategories as $name) {
                $category = new Category();
                $category->name = $name;
                $category->appendTo($root);
            }
        }
    }

    public function down()
    {
        $this->delete('{{%category}}');
        $this->execute('ALTER TABLE {{%category}} AUTO_INCREMENT = 1');
    }
}
