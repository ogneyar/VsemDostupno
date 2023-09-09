<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subscriber_payment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created_at
 * @property integer $number_of_times
 *
 * @property User $user
 * @property string $fullName
 * 
 */
class SubscriberPayment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriber_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'number_of_times'], 'integer'],
            [['created_at'], 'safe'],
            // [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'user_id' => 'Идентификатор пользователя',
            'created_at' => 'Дата и время платежа',
            'number_of_times' => 'Который раз не оплатил?',
            'fullName' => 'ФИО',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getFullName()
    {
        return $this->user->fullName;
    }

    public function getRole()
    {
        return $this->user->role;
    }

    public function getMember()
    {
        return Member::find()->where(['user_id' => $this->user->id])->one();
    }

    public function getPartner()
    {
        return Partner::find()->where(['id' => $this->member->partner_id])->one();
    }

}
