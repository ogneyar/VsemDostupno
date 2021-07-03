<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service_has_photo".
 *
 * @property integer $id
 * @property integer $service_id
 * @property integer $photo_id
 *
 * @property Photo $photo
 * @property string $thumbUrl
 * @property string $imageUrl
 */
class ServiceHasPhoto extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service_has_photo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['service_id', 'photo_id'], 'required'],
            [['service_id', 'photo_id'], 'integer'],
            [['service_id', 'photo_id'], 'unique', 'targetAttribute' => ['service_id', 'photo_id'], 'message' => 'The combination of Идентификатор услуги and Идентификатор изображения has already been taken.'],
            [['photo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Photo::className(), 'targetAttribute' => ['photo_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'service_id' => 'Идентификатор услуги',
            'photo_id' => 'Идентификатор изображения',
        ];
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
