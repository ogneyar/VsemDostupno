<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "provider_has_category".
 *
 * @property integer $id
 * @property integer $provider_id
 * @property integer $category_id
 *
 * @property Category $category
 * @property Provider $provider
 */
class ProviderHasCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider_has_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['provider_id', 'category_id'], 'required'],
            [['provider_id', 'category_id'], 'integer'],
            [['provider_id', 'category_id'], 'unique', 'targetAttribute' => ['provider_id', 'category_id'], 'message' => 'The combination of Идентификатор поставщика and Идентификатор категории has already been taken.'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['provider_id'], 'exist', 'skipOnError' => true, 'targetClass' => Provider::className(), 'targetAttribute' => ['provider_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'provider_id' => 'Идентификатор поставщика',
            'category_id' => 'Идентификатор категории',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id']);
    }
    
    public static function getCategoriesByProvider($provider_id)
    {
        return self::find()->where(['provider_id' => $provider_id])->with('category')->all();
    }
}
