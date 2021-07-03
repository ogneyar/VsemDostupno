<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "forgot".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 *
 * @property User $user
 * @property string $url
 */
class Forgot extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forgot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['user_id'], 'unique']
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
            'token' => 'Токен',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->token = sha1(Yii::$app->params['secret'] . serialize($this->user) . rand());

            return true;
        }

        return false;
    }

    public function getUrl()
    {
        return Url::to(['/profile/forgot-change', 'token' => $this->token], true);
    }
}
