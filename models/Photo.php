<?php

namespace app\models;

use Yii;
use app\models\Image;
use yii\imagine;

/**
 * This is the model class for table "photo".
 *
 * @property integer $id
 * @property integer $image_id
 * @property integer $thumb_id
 *
 * @property Image $thumb
 * @property Image $image
 */
class Photo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'photo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_id', 'thumb_id'], 'required'],
            [['image_id', 'thumb_id'], 'integer'],
            [['image_id', 'thumb_id'], 'unique', 'targetAttribute' => ['image_id', 'thumb_id'], 'message' => 'The combination of Идентификатор изображения and Идентификатор изображения для предпросмотра has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'image_id' => 'Идентификатор изображения',
            'thumb_id' => 'Идентификатор изображения для предпросмотра',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThumb()
    {
        return $this->hasOne(Image::className(), ['id' => 'thumb_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::className(), ['id' => 'image_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->image->delete();
            $this->thumb->delete();
            return true;
        } else {
            return false;
        }
    }

    public function getImageUrl()
    {
        return $this->image->getUrl();
    }

    public function getThumbUrl()
    {
        return $this->thumb->getUrl();
    }

    protected function updateImage($maxSize, $file)
    {
        $image = imagine\Image::getImagine();
        $image = $image->open($file);

        $size = $image->getSize();
        $size = ($size->getWidth() > $size->getHeight()) ?
            $size->widen($maxSize):
            $size->heighten($maxSize);

        $fileName = Yii::getAlias('@webroot' . $this->image->file);
        if (!file_exists(dirname($fileName))) {
            mkdir(dirname($fileName), 0777, true);
        }
        $image->resize($size)->save($fileName);

        return $this;
    }

    protected function updateThumb($width, $height, $file)
    {
        $image = imagine\Image::getImagine();
        $image = $image->open($file);

        $thumbName = Yii::getAlias('@webroot' . $this->thumb->file);
        if (!file_exists(dirname($thumbName))) {
            mkdir(dirname($thumbName), 0777, true);
        }
        //imagine\Image::thumbnail($file, $width, $height, \Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET)->save($thumbName);
        imagine\Image::thumbnail($file, $width, $height, \Imagine\Image\ManipulatorInterface::THUMBNAIL_OUTBOUND)->save($thumbName);

        return $this;
    }

    public function updatePhoto($maxSize, $thumbWidth, $thumbHeight, $file)
    {
        $this->updateImage($maxSize, $file);

        $imageName = Yii::getAlias('@webroot' . $this->image->file);
        $this->updateThumb($thumbWidth, $thumbHeight, $imageName);

        return $this;
    }

    public static function createPhoto($maxSize, $thumbWidth, $thumbHeight, $file)
    {
        $image = new Image();
        $image->save();
        $thumb = new Image();
        $thumb->save();

        $photo = new self();
        $photo->image_id = $image->id;
        $photo->thumb_id = $thumb->id;
        $photo->save();

        $photo->updatePhoto($maxSize, $thumbWidth, $thumbHeight, $file);

        return $photo;
    }
}
