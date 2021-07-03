<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "o_view".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $section
 * @property string $dts
 * @property string $dte
 * @property string $detail
 *
 * @property User $user
 */
class OView extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'o_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'section', 'dts', 'detail'], 'required'],
            [['user_id'], 'integer'],
            [['section', 'detail'], 'string'],
            [['dts', 'dte'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'section' => 'Section',
            'dts' => 'Dts',
            'dte' => 'Dte',
            'detail' => 'Detail',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
