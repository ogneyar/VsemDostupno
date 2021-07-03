<?php

use yii\db\Schema;
use yii\db\Migration;

class m160305_201021_create_order_table extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%order}}', [
            'id' => Schema::TYPE_INTEGER . "(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор'",
            'created_at' => Schema::TYPE_TIMESTAMP . " NOT NULL DEFAULT NOW() COMMENT 'Дата и время создания'",
            'city_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор города'",
            'partner_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор партнера'",
            'user_id' => Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор пользователя'",
            'role' => "ENUM('admin', 'member', 'partner') COMMENT 'Роль'",
            'city_name' => Schema::TYPE_STRING . '(255) NOT NULL COMMENT "Название города"',
            'partner_name' => Schema::TYPE_STRING . '(255) COMMENT "Название партнера"',
            'email' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Емайл'",
            'phone' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Телефон'",
            'firstname' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Имя'",
            'lastname' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Фамилия'",
            'patronymic' => Schema::TYPE_STRING . "(255) NOT NULL COMMENT 'Отчество'",
            'address' => Schema::TYPE_TEXT . " COMMENT 'Адрес доставки'",
            'total' => Schema::TYPE_MONEY . "(19,2) NOT NULL COMMENT 'Стоимость'",
            'comment' => Schema::TYPE_TEXT . " COMMENT 'Комментарий'",
            'PRIMARY KEY (id)',
        ], $tableOptions . " COMMENT 'Заказ'");

        $this->execute('ALTER TABLE {{%order}} AUTO_INCREMENT = 1000');

        $this->createIndex('idx_order_id', '{{%order}}', 'id');
        $this->createIndex('idx_order_city_id', '{{%order}}', 'city_id');
        $this->createIndex('idx_order_partner_id', '{{%order}}', 'partner_id');
        $this->createIndex('idx_order_user_id', '{{%order}}', 'user_id');

        $this->addForeignKey('fk_order_city_id', '{{%order}}', 'city_id', '{{%city}}', 'id');
        $this->addForeignKey('fk_order_partner_id', '{{%order}}', 'partner_id', '{{%partner}}', 'id');
        $this->addForeignKey('fk_order_user_id', '{{%order}}', 'user_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_order_city_id', '{{%order}}');
        $this->dropForeignKey('fk_order_partner_id', '{{%order}}');
        $this->dropForeignKey('fk_order_user_id', '{{%order}}');

        $this->dropIndex('idx_order_id', '{{%order}}');
        $this->dropIndex('idx_order_city_id', '{{%order}}');
        $this->dropIndex('idx_order_partner_id', '{{%order}}');
        $this->dropIndex('idx_order_user_id', '{{%order}}');

        $this->dropTable('{{%order}}');
    }
}
