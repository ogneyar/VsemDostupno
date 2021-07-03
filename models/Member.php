<?php

namespace app\models;

use Yii;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $partner_id
 * @property  boolean $become_provider
 *
 * @property Partner $partner
 * @property User $user
 * @property string $cityName
 * @property string $partnerName
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'partner_id'], 'required'],
            [['user_id', 'partner_id'], 'integer'],
            [['user_id'], 'unique'],
            [['become_provider'],'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $user = new User();

        return ArrayHelper::merge([
                'id' => 'Идентификатор',
                'user_id' => 'Идентификатор пользователя',
                'partner_id' => 'Идентификатор партнера',
                'cityName' => 'Название города',
                'partnerName' => 'Название партнера',
                'become_provider'=>'Стать поставщиком?',
            ],
            $user->attributeLabels()
        );
    }

    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $e) {
            if ($this->user) {
                return $this->user->$name;
            }
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['id' => 'partner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getPartnerName()
    {
        return $this->partner->name;
    }

    public function getCityName()
    {
        return isset($this->partner->city->name) ? $this->partner->city->name : '';
    }
}
