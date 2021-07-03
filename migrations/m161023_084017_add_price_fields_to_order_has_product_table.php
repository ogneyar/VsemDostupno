<?php

use yii\db\Schema;
use yii\db\Migration;

class m161023_084017_add_price_fields_to_order_has_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%order_has_product}}', 'purchase_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Закупочная цена"');
        $this->addColumn('{{%order_has_product}}', 'storage_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Складской сбор"');
        $this->addColumn('{{%order_has_product}}', 'invite_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Отчисление рекомендателю"');
        $this->addColumn('{{%order_has_product}}', 'fraternity_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Отчисление в фонд Содружества"');
        $this->addColumn('{{%order_has_product}}', 'group_price', Schema::TYPE_MONEY . '(19,2) NOT NULL COMMENT "Отчисление в фонд Группы"');
    }

    public function down()
    {
        $this->dropColumn('{{%order_has_product}}', 'purchase_price');
        $this->dropColumn('{{%order_has_product}}', 'storage_price');
        $this->dropColumn('{{%order_has_product}}', 'invite_price');
        $this->dropColumn('{{%order_has_product}}', 'fraternity_price');
        $this->dropColumn('{{%order_has_product}}', 'group_price');
    }
}
