<?php

use yii\db\Schema;
use yii\db\Migration;

class m170524_052525_add_fields_for_product_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%product}}', 'only_member_purchase', Schema::TYPE_BOOLEAN . '(1) DEFAULT 0 COMMENT "Товар для участников"');
        $this->addColumn('{{%product}}', 'expiry_timestamp', Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT "0" COMMENT "Срок годности"');
        $this->addColumn('{{%product}}', 'weight', Schema::TYPE_DECIMAL . '(10,3) COMMENT "Вес"');
        $this->addColumn('{{%product}}', 'min_inventory', Schema::TYPE_INTEGER . '(11) COMMENT "Минимальный запас"');
    }

    public function down()
    {
        $this->dropColumn('{{%product}}', 'only_member_purchase');
        $this->dropColumn('{{%product}}', 'expiry_timestamp');
        $this->dropColumn('{{%product}}', 'weight');
        $this->dropColumn('{{%product}}', 'min_inventory');
    }
}
