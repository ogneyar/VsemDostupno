<?php

use yii\db\Schema;
use yii\db\Migration;

class m161012_154400_modify_recommenders_fields_of_user_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%user}}', 'recommender', Schema::TYPE_STRING . "(255) COMMENT 'Информация о рекомендателе'");
        $this->renameColumn('{{%user}}', 'recommender', 'recommender_info');
        $this->addColumn('{{%user}}', 'recommender_id', Schema::TYPE_INTEGER . "(11) COMMENT 'Идентификатор рекомендателя'");

        $this->createIndex('idx_user_recommender_id', '{{%user}}', 'recommender_id');
        $this->addForeignKey('fk_user_recommender_id', '{{%user}}', 'recommender_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->renameColumn('{{%user}}', 'recommender_info', 'recommender');
        $this->alterColumn('{{%user}}', 'recommender', Schema::TYPE_STRING . "(255) COMMENT 'Рекомендатель'");

        $this->dropForeignKey('fk_user_recommender_id', '{{%user}}');
        $this->dropIndex('idx_user_recommender_id', '{{%user}}');

        $this->dropColumn('{{%user}}', 'recommender_id');
    }
}
