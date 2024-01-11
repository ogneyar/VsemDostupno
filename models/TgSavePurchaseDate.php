<?php

namespace app\models;

use Yii;
// use app\models\User;

/**
 * This is the model class for table "tg_save_purchase_date".
 *
 * @property integer $id
 * @property integer $chat_id
 * @property integer $purchase_date
 *
 * @property User $chatId
 */
class TgSavePurchaseDate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tg_save_purchase_date';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chat_id', 'purchase_date'], 'required'],
            [['chat_id', 'purchase_date'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'chat_id' => 'Идентификатор пользователя телеграм',
            'purchase_date' => 'Дата закупки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChatId()
    {
        return $this->hasOne(User::className(), ['id' => 'chat_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseDate()
    {
        return $this->hasOne(User::className(), ['id' => 'purchase_date']);
    }

}
