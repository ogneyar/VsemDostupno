<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $file
 *
 * @property Photo[] $photos
 */
class Image extends \yii\db\ActiveRecord
{
    const IMAGE_EXT = 'jpg';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file'], 'safe'],
            [['file'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'file' => 'Путь к файлу',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['thumb_id' => 'id']);
    }

    public function getUrl()
    {
        if ($this->file) {
            $time = filemtime(Yii::getAlias('@webroot' . $this->file));
            return Url::to([$this->file, 'v' => md5($time . Yii::$app->params['secret'])]);
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $path = sprintf('%08x', $this->id);
            $path = preg_replace('/^(.{2})(.{2})(.{2})(.{2})$/', '$1/$2/$3/$4', $path);
            $this->file = sprintf(
                '%s/%s.%s',
                Yii::$app->params['imagesStorePath'], $path, self::IMAGE_EXT
            );
            $this->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            @unlink(Yii::getAlias('@webroot' . $this->file));
            return true;
        } else {
            return false;
        }
    }
}
