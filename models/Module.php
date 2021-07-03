<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "module".
 *
 * @property integer $id
 * @property string $name
 * @property integer $state
 */
class Module extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'module';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['state'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'state' => 'Вкл./Выкл.',
            'description' => 'Описание',
        ];
    }
}
