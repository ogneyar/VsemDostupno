<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Category;

class m151219_112734_fix_data_in_category_table extends Migration
{
    public function up()
    {
        foreach (Category::find()->each() as $category) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $category->name)) {
                $category->purchase_timestamp = mb_substr($category->name, 0, 10, Yii::$app->charset);
                $category->order_timestamp = date('Y-m-d', strtotime($category->purchase_timestamp) - 3 * 24 * 60 *60);
                $category->name = trim(mb_substr($category->name, 10, null, Yii::$app->charset));
                $category->saveNode();
            }
        }
    }

    public function down()
    {
        foreach (Category::find()->each() as $category) {
            if (strtotime($category->purchase_timestamp) > 0) {
                $category->name = mb_substr($category->purchase_timestamp, 0, 10, Yii::$app->charset) . ' ' . $category->name;
                $category->purchase_timestamp =
                $category->order_timestamp = 0;
                $category->saveNode();
            }
        }
    }
}
