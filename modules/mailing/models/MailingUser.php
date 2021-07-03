<?php

namespace app\modules\mailing\models;

use Yii;
use app\models\User;

/**
 * This is the model class for table "mailing_user".
 *
 * @property integer $id
 * @property integer $mailing_category_id
 * @property integer $user_id
 *
 * @property MailingCategory $mailingCategory
 * @property User $user
 */
class MailingUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mailing_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mailing_category_id', 'user_id'], 'required'],
            [['mailing_category_id', 'user_id'], 'integer'],
            [['mailing_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => MailingCategory::className(), 'targetAttribute' => ['mailing_category_id' => 'id']],
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
            'mailing_category_id' => 'Категория рассылки',
            'user_id' => 'Пользователь',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailingCategory()
    {
        return $this->hasOne(MailingCategory::className(), ['id' => 'mailing_category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
