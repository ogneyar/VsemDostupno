<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles adding published_field to table `product_table`.
 */
class m160728_105634_add_published_field_to_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%product}}', 'published', Schema::TYPE_BOOLEAN . '(1) DEFAULT 0 COMMENT "Опубликованный"');
        $this->execute('UPDATE {{%product}} SET published = 1');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%product}}', 'published');
    }
}
