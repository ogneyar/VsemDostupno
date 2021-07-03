<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category_has_product".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $product_id
 *
 * @property Product $product
 * @property Category $category
 */
class CategoryHasProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_has_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'product_id'], 'required'],
            [['category_id', 'product_id'], 'integer'],
            [['category_id', 'product_id'], 'unique', 'targetAttribute' => ['category_id', 'product_id'], 'message' => 'The combination of Идентификатор категории and Идентификатор товара has already been taken.']
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
            'product_id' => 'Идентификатор товара',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
}
