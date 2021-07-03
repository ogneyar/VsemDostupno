<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Category;

class m151123_204716_add_data_to_category_table extends Migration
{
    public function up()
    {
        $categories = $this->getCategories();

        foreach ($categories as $name => $slug) {
            $category = new Category;
            $category->name = $name;
            $category->slug = $slug;
            $category->saveNode();
        }

        $purchase = Category::findOne(['slug' => 'zakupki']);
        $category = new Category();
        $category->name = date('Y-m-d', time() + 7 * 24 * 60 * 60);
        $category->appendTo($purchase);
        $purchase = $category;
        for ($count = 1; $count < 10; $count++) {
            $category = new Category();
            $category->name = date('Y-m-d', time() + ($count + 7) * 24 * 60 * 60);
            $category->insertAfter($purchase);
            $purchase = $category;
        }
    }

    public function down()
    {
        $categories = $this->getCategories();

        foreach ($categories as $name => $slug) {
            $category = Category::findOne(['slug' => $slug]);
            if ($category) {
                $category->deleteNode();
            }
        }
    }

    private function getCategories()
    {
        return [
            'Спецпредложения' => 'specpredlozheniya',
            'Новинки' => 'novinki',
            'Закупки' => 'zakupki',
        ];
    }
}
