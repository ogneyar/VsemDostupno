<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "mailing_message".
 *
 * @property integer $id
 * @property string $category
 * @property integer $user_id
 * @property string $subject
 * @property string $message
 * @property string $sent_date
 * @property integer $answered
 *
 * @property User $user
 */
class MailingMessage extends \yii\db\ActiveRecord
{
    const CATEGORY_QUESTION = 'question';
    const CATEGORY_CLAIM = 'claim';
    const CATEGORY_PROPOSAL = 'proposal';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'user_id', 'subject', 'message'], 'required'],
            [['category', 'message'], 'string'],
            [['user_id', 'answered'], 'integer'],
            [['sent_date'], 'safe'],
            [['subject'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'category' => 'Категория сообщения',
            'user_id' => 'Пользователь',
            'subject' => 'Тема',
            'message' => 'Сообщение',
            'sent_date' => 'Время отправки',
            'answered' => 'Отвечено',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function getCategoryText($cat)
    {
        $catsText = [
            self::CATEGORY_QUESTION => 'Вопрос',
            self::CATEGORY_CLAIM => 'Жалоба',
            self::CATEGORY_PROPOSAL => 'Предложение'
        ];
        
        return $catsText[$cat];
    }
    
    public function getCategoryTextRaw()
    {
        $catsText = [
            self::CATEGORY_QUESTION => 'Вопрос',
            self::CATEGORY_CLAIM => 'Жалоба',
            self::CATEGORY_PROPOSAL => 'Предложение'
        ];
        
        return $catsText[$this->category];
    }
}
