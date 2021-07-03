<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "order_status".
 *
 * @property integer $id
 * @property string $type
 * @property string $name
 */
class OrderStatus extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_CANCELED = 'cancled';
    const STATUS_VERIFIED = 'verified';
    const STATUS_COMPLETED = 'completed';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['type', 'name'], 'string', 'max' => 255],
            [['type'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'type' => 'Тип',
            'name' => 'Название',
        ];
    }

    public static function getIdByType($type)
    {
        $orderStatus = self::findOne(['type' => $type]);

        return $orderStatus->id;
    }
}
