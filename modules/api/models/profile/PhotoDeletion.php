<?php

namespace app\modules\api\models\profile;

use Yii;
use yii\base\Model;
use app\models\Category;
use app\models\Product;
use app\models\Service;

/**
 * This is the model class for photo deletion.
 *
 */
class PhotoDeletion extends Model
{
    public $key;
    public $id;
    public $class;
    public $manufacturer;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'id', 'class'], 'required'],
            [['key', 'id'], 'integer'],
            [['class'], 'in', 'range' => [
                Category::className(),
                Product::className(),
                Service::className(),
            ]],
            [['manufacturer'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => 'Идентификатор фотографии',
            'id' => 'Идентификатор объекта',
            'class' => 'Класс объекта',
        ];
    }
}
