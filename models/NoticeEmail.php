<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notice_email".
 *
 * @property integer $id
 * @property string $email
 */
class NoticeEmail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notice_email';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
        ];
    }
    
    public static function getEmails()
    {
        $emails = self::find()->all();
        if ($emails) {
            $res = [];
            foreach ($emails as $email) {
                $res[] = $email->email;
            }
            return $res;
        }
        return false;
    }
}
