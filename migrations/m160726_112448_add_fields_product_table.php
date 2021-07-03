<?php

use yii\db\Schema;
use yii\db\Migration;

class m160726_112448_add_fields_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'composition', Schema::TYPE_TEXT . ' COMMENT "Сотстав"');
        $this->addColumn('{{%product}}', 'packing', Schema::TYPE_TEXT . ' COMMENT "Фасовка"');
        $this->addColumn('{{%product}}', 'manufacturer', Schema::TYPE_TEXT . ' COMMENT "Производитель"');
        $this->addColumn('{{%product}}', 'status', Schema::TYPE_TEXT . ' COMMENT "Статус продукта"');
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'composition');
        $this->dropColumn('{{%product}}', 'packing');
        $this->dropColumn('{{%product}}', 'manufacturer');
        $this->dropColumn('{{%product}}', 'status');
    }
}
