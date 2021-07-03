<?php

use yii\db\Migration;
use app\models\Page;

class m160423_054344_rename_page_slug extends Migration
{
    public function up()
    {
        $page = Page::findOne(['slug' => 'pomoshch']);

        if ($page) {
            $page->slug = 'pravila';
            $page->title = 'Правила';
            $page->save();
        }
    }

    public function down()
    {
        $page = Page::findOne(['slug' => 'pravila']);

        if ($page) {
            $page->slug = 'pomoshch';
            $page->title = 'Помощь';
            $page->save();
        }
    }
}
