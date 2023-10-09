<?php

namespace app\models;

use Yii;
// use app\models\User;

/**
 * This is the model class for table "tg_communication".
 *
 * @property integer $id
 * @property integer $chat_id
 * @property integer $to_chat_id
 *
 * @property User $chatId
 * @property User $toChatId
 */
class TgCommunication extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tg_communication';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chat_id', 'to_chat_id'], 'required'],
            [['chat_id', 'to_chat_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'chat_id' => 'От кого сообщение',
            'to_chat_id' => 'Кому сообщение',
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
    public function getToChatId()
    {
        return $this->hasOne(User::className(), ['id' => 'to_chat_id']);
    }

}
