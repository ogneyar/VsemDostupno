<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "candidate".
 *
 * @property integer $id
 * @property string $email
 * @property string $firstname
 * @property string $lastname
 * @property string $patronymic
 * @property string $birthdate
 * @property string $phone
 * @property integer $block_mailing
 */
class Candidate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'candidate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id'], 'required'],
            [['birthdate'], 'safe'],
            [['block_mailing'], 'integer'],
            [['email'], 'string', 'max' => 100],
            [['fio'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['comment'], 'string'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => CandidateGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'email' => 'Email',
            'fio' => 'ФИО',
            'birthdate' => 'Дата рождения',
            'phone' => 'Телефон',
            'block_mailing' => 'Блокировать рассылку',
            'group_id' => 'Группа',
            'comment' => 'Примечание',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(CandidateGroup::className(), ['id' => 'group_id']);
    }
    
    public static function isCandidate($params)
    {
        $res = self::find()->where([
            'email' => $params['email'], 
        ])->one();
        
        if ($res) {
            return Url::to(['/admin/candidate/view', 'id' => $res->id], true);
        }
        return false;
    }
    
    public static function getLastInserted()
    {
        $last_id = self::find()->max('id');
        $res = self::findOne($last_id);
        return $res;
    }
}
