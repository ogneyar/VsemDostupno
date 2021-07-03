<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "transfer".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $from_account_id
 * @property integer $to_account_id
 * @property string $amount
 * @property string $message
 * @property string $token
 *
 * @property Account $fromAccount
 * @property Account $toAccount
 * @property string $url
 */
class Transfer extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transfer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['from_account_id', 'to_account_id', 'amount', 'message', 'token'], 'required'],
            [['from_account_id', 'to_account_id'], 'integer'],
            [['amount'], 'number'],
            [['message'], 'string'],
            [['token'], 'string', 'max' => 255],
            [['from_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['from_account_id' => 'id']],
            [['to_account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['to_account_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'created_at' => 'Дата операции',
            'from_account_id' => 'Идентификатор счета-источнка',
            'to_account_id' => 'Идентификатор счета-приемника',
            'amount' => 'Сумма',
            'message' => 'Сообщение',
            'token' => 'Токен',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'from_account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'to_account_id']);
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->token = sha1(Yii::$app->params['secret'] . serialize($this->fromAccount) . rand());

            return true;
        }

        return false;
    }

    public function getUrl()
    {
        return Url::to(['/profile/account/transfer', 'token' => $this->token], true);
    }
}
