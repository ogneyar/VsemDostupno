<?php

use yii\db\Schema;
use yii\db\Migration;

class m151024_131845_create_photo_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%photo}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'image_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор изображения'",
            'thumb_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор изображения для предпросмотра'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_photo_key (image_id, thumb_id)',
        ], $tableOptions . " COMMENT 'Фотография'");

        $this->createIndex('idx_photo_id', '{{%photo}}', 'id');
        $this->createIndex('idx_photo_image_id', '{{%photo}}', 'image_id');
        $this->createIndex('idx_photo_thumb_id', '{{%photo}}', 'thumb_id');

        $this->addForeignKey('fk_photo_image_id', '{{%photo}}', 'image_id', '{{%image}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_photo_thumb_id', '{{%photo}}', 'thumb_id', '{{%image}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_photo_image_id', '{{%photo}}');
        $this->dropForeignKey('fk_photo_thumb_id', '{{%photo}}');

        $this->dropIndex('idx_photo_id', '{{%photo}}');
        $this->dropIndex('idx_photo_image_id', '{{%photo}}');
        $this->dropIndex('idx_photo_thumb_id', '{{%photo}}');

        $this->dropTable('{{%photo}}');
    }
}
