<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property integer $visibility
 * @property string $slug
 * @property string $title
 * @property string $content
 *
 * @property string $url
 */
class Page extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'page';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['visibility'], 'integer'],
            [['title', 'content'], 'required'],
            [['content'], 'string'],
            [['slug', 'title'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'visibility' => 'Видимость',
            'slug' => 'Заголовок для URL',
            'title' => 'Заголовок',
            'content' => 'Содержимое',
        ];
    }

    public function getUrl()
    {
        return Url::to(['/page/' . ($this->slug ? $this->slug : $this->id)]);
    }
}
