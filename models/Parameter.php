<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "parameter".
 *
 * @property integer $id
 * @property string $name
 * @property string $value
 */
class Parameter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parameter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['value'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'name' => 'Название',
            'value' => 'Значние',
        ];
    }

    public static function getValueByName($name)
    {
        $parameter = self::findOne(['name' => $name]);

        return $parameter ? $parameter->value : '';
    }

    public static function setValueByName($name, $value)
    {
        $parameter = self::findOne(['name' => $name]);

        if ($parameter) {
            $parameter->value = $value;
            $parameter->save();
        }
    }
}
