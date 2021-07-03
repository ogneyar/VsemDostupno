<?php

namespace app\models;

use Yii;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * This is the model class for table "partner".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $city_id
 * @property string $name
 *
 * @property Member[] $members
 * @property City $city
 * @property User $user
 * @property string $cityName
 */
class Partner extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'city_id', 'name'], 'required'],
            [['user_id', 'city_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['address'], 'string'],
            [['user_id'], 'unique'],
            [['city_id', 'name'], 'unique', 'targetAttribute' => ['city_id', 'name'], 'message' => 'The combination of Идентификатор города and Название has already been taken.']
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
                'city_id' => 'Идентификатор города',
                'cityName' => 'Город',
                'name' => 'Название',
                'address' => 'Адрес для поставщика'
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
     * @return \yii\db\ActiveQuery
     */
    public function getMembers()
    {
        return $this->hasMany(Member::className(), ['partner_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCityName()
    {
        return isset($this->city->name) ? $this->city->name : '';
    }

    public function getGroup()
    {
        return $this->user->getAccount(Account::TYPE_GROUP);
    }
    
    public static function getByUserId($user_id)
    {
        return self::findOne(['user_id' => $user_id]);
    }
}
