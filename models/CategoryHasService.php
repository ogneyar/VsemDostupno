<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_has_service".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $service_id
 *
 * @property Service $service
 * @property Category $category
 */
class CategoryHasService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_has_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'service_id'], 'required'],
            [['category_id', 'service_id'], 'integer'],
            [['category_id', 'service_id'], 'unique', 'targetAttribute' => ['category_id', 'service_id'], 'message' => 'The combination of Идентификатор категории and Идентификатор услуги has already been taken.'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'category_id' => 'Идентификатор категории',
            'service_id' => 'Идентификатор услуги',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
}
