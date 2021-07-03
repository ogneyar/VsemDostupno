<?php

use yii\db\Migration;
use app\models\Category;

class m160906_025116_add_slug_for_service_category extends Migration
{
    public function up()
    {
        $category = Category::findOne(['name' => 'Услуги']);
        $category->slug = Category::SERVICE_SLUG;
        $category->saveNode();
    }

    public function down()
    {
        $category = Category::findOne(['name' => 'Услуги']);
        $category->slug = '';
        $category->saveNode();
    }
}
