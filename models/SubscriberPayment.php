<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "subscriber_payment".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $created_at
 * @property string $amount
 *
 * @property User $user
 * @property string $fullName
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
            [['user_id', 'amount'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getFullName()
    {
        return $this->user->fullName;
    }
}
