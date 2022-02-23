<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subscriber_messages".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created_at
 * @property integer $amount
 * 
 */
class SubscriberMessages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'subscriber_messages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'amount'], 'required'],
            [['user_id'], 'integer'],
            [['amount'], 'number'],
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
            'user_id' => 'Идентификатор пользователя',
            'created_at' => 'Дата и время платежа',
            'amount' => 'Сумма',
            'fullName' => 'ФИО',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        // return $this->hasOne(User::className(), ['id' => 'user_id']);
        return User::find()->where(['id' => $this->user_id])->one();
    }

    public function getFullName()
    {
        return $this->user ? $this->user->fullName : null;
    }

    public function getRole()
    {
        return $this->user ? $this->user->role : null;
    }

    public function getMember()
    {
        return $this->user ? Member::find()->where(['user_id' => $this->user->id])->one() : null;
    }

    public function getPartner()
    {
        return $this->member ? Partner::find()->where(['id' => $this->member->partner_id])->one() : null;
    }

    public function getSubscriber()
    {
        return $this->user ? SubscriberPayment::find()->where(['user_id' => $this->user->id])->one() : null;
    }
}
