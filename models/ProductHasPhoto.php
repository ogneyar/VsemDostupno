<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "product_has_photo".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $photo_id
 *
 * @property Photo $photo
 * @property string $thumbUrl
 * @property string $imageUrl
 */
class ProductHasPhoto extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_has_photo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'photo_id'], 'required'],
            [['product_id', 'photo_id'], 'integer'],
            [['product_id', 'photo_id'], 'unique', 'targetAttribute' => ['product_id', 'photo_id'], 'message' => 'The combination of Идентификатор товара and Идентификатор изображения has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'product_id' => 'Идентификатор товара',
            'photo_id' => 'Идентификатор изображения',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->photo->delete(true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoto()
    {
        return $this->hasOne(Photo::className(), ['id' => 'photo_id']);
    }

    public function getImageUrl()
    {
        return $this->photo->imageUrl;
    }

    public function getThumbUrl()
    {
        return $this->photo->thumbUrl;
    }
}
