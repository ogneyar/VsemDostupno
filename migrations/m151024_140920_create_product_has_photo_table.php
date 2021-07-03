<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_140920_create_product_has_photo_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%product_has_photo}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'product_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор товара'",
            'photo_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор изображения'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_product_has_photo_key (product_id, photo_id)',
        ], $tableOptions . " COMMENT 'Фотография товарова'");

        $this->createIndex('idx_product_has_photo_id', '{{%product_has_photo}}', 'id');
        $this->createIndex('idx_product_has_photo_product_id', '{{%product_has_photo}}', 'product_id');
        $this->createIndex('idx_product_has_photo_photo_id', '{{%product_has_photo}}', 'photo_id');

        $this->addForeignKey('fk_product_has_photo_photo_id', '{{%product_has_photo}}', 'photo_id', '{{%photo}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_product_has_photo_photo_id', '{{%product_has_photo}}');

        $this->dropIndex('idx_product_has_photo_id', '{{%product_has_photo}}');
        $this->dropIndex('idx_product_has_photo_product_id', '{{%product_has_photo}}');
        $this->dropIndex('idx_product_has_photo_photo_id', '{{%product_has_photo}}');

        $this->dropTable('{{%product_has_photo}}');
    }
}
