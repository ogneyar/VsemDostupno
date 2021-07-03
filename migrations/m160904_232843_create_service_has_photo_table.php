<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Handles the creation for table `service_has_photo`.
 */
class m160904_232843_create_service_has_photo_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%service_has_photo}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'service_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор услуги'",
            'photo_id' => Schema::TYPE_INTEGER . "(11) NOT NULL COMMENT 'Идентификатор изображения'",
            'PRIMARY KEY (id)',
            'UNIQUE KEY unique_service_has_photo_key (service_id, photo_id)',
        ], $tableOptions . " COMMENT 'Фотография услуги'");

        $this->createIndex('idx_service_has_photo_id', '{{%service_has_photo}}', 'id');
        $this->createIndex('idx_service_has_photo_service_id', '{{%service_has_photo}}', 'service_id');
        $this->createIndex('idx_service_has_photo_photo_id', '{{%service_has_photo}}', 'photo_id');

        $this->addForeignKey('fk_service_has_photo_photo_id', '{{%service_has_photo}}', 'photo_id', '{{%photo}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_service_has_photo_photo_id', '{{%service_has_photo}}');

        $this->dropIndex('idx_service_has_photo_id', '{{%service_has_photo}}');
        $this->dropIndex('idx_service_has_photo_service_id', '{{%service_has_photo}}');
        $this->dropIndex('idx_service_has_photo_photo_id', '{{%service_has_photo}}');

        $this->dropTable('{{%service_has_photo}}');
    }
}
