<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "mailing_vote_stat".
 *
 * @property integer $id
 * @property integer $mailing_vote_id
 * @property integer $user_id
 * @property string $vote
 * @property string $vote_date
 *
 * @property MailingVote $mailingVote
 * @property User $user
 */
class MailingVoteStat extends \yii\db\ActiveRecord
{
    const VOTE_AGREE = 'agree';
    const VOTE_AGAINST = 'against';
    const VOTE_HOLD = 'hold';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_vote_stat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mailing_vote_id', 'user_id', 'vote'], 'required'],
            [['mailing_vote_id', 'user_id'], 'integer'],
            [['vote'], 'string'],
            [['vote_date'], 'safe'],
            [['mailing_vote_id'], 'exist', 'skipOnError' => true, 'targetClass' => MailingVote::className(), 'targetAttribute' => ['mailing_vote_id' => 'id']],
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
            'mailing_vote_id' => 'Голосование',
            'user_id' => 'Пользователь',
            'vote' => 'Выбор',
            'vote_date' => 'Время голосования',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingVote()
    {
        return $this->hasOne(MailingVote::className(), ['id' => 'mailing_vote_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function getVoteByUser($user_id, $vote_id)
    {
        $vote = self::find()->where(['user_id' => $user_id, 'mailing_vote_id' => $vote_id])->one();
        
        return self::getVoteText($vote->vote);
    }
    
    public static function getVoteText($vote)
    {
        $votesText = [
            self::VOTE_AGREE => 'За',
            self::VOTE_AGAINST => 'Против',
            self::VOTE_HOLD => 'Воздерживаюсь'
        ];
        
        return $votesText[$vote];
    }
    
    public static function getTotalCountByVote($vote_id)
    {
        return self::find()->where(['mailing_vote_id' => $vote_id])->count();
    }
    
    public static function getTotalByVote($vote_id, $vote)
    {
        return self::find()->where(['mailing_vote_id' => $vote_id, 'vote' => $vote])->count();
    }
    
    public static function getVotedUsers($vote_id, $vote = "")
    {
        $where = empty($vote) ? ['mailing_vote_id' => $vote_id] : ['mailing_vote_id' => $vote_id, 'vote' => $vote];
        return self::find()->where($where)->all();
    }
}
