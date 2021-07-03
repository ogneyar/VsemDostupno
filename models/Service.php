<?php

namespace app\models;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\base\Exception;
use app\models\User;

/**
 * This is the model class for table "service".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $visibility
 * @property integer $published
 * @property string $name
 * @property string $description
 * @property string $price
 * @property string $contacts
 * @property string $member_price
 *
 * @property CategoryHasService[] $categoryHasService
 * @property Category[] $categories
 * @property User $user
 * @property string $categoryIds
 * @property string $url
 * @property string $thumbUrl
 * @property string $imageUrl
 * @property string $calculatedPrice
 * @property string $formattedPrice
 * @property string $formattedMemberPrice
 * @property string $formattedCalculatedPrice
 */
class Service extends \yii\db\ActiveRecord
{
    public $categoryIds;
    public $gallery; /* dummy property */

    const MAX_FILE_COUNT = 10;
    const MAX_GALLERY_IMAGE_SIZE = 1024;
    const MAX_GALLERY_THUMB_WIDTH = 500;
    const MAX_GALLERY_THUMB_HEIGHT = 500;
    const DEFAULT_IMAGE = '/images/default-image.jpg';
    const DEFAULT_THUMB = '/images/default-thumb.jpg';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'description'], 'required'],
            [['user_id', 'visibility', 'published'], 'integer'],
            [['price', 'member_price'], 'required', 'when' => function ($model) {return !empty($model->price) || !empty($model->member_price);}, 'whenClient' => "function (attribute, value) {return $('input[name=\"Service[price]\"]').val() || $('input[name=\"Service[member_price]\"]').val();}"],
            [['description'], 'string'],
            [['price', 'member_price'], 'number'],
            [['name', 'contacts'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['categoryIds', 'gallery'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'user_id' => 'Идентификатор пользователя',
            'visibility' => 'Видимость',
            'published' => 'Опубликованная',
            'name' => 'Название',
            'description' => 'Описание',
            'price' => 'Цена для всех',
            'contacts' => 'Контакты',
            'member_price' => 'Цена для участников',
            'categoryIds' => 'Категории',
            'gallery' => 'Фотографии',
            'thumbUrl' => 'Фотография',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            foreach ($this->serviceHasPhoto as $serviceHasPhoto) {
                $serviceHasPhoto->delete();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryHasService()
    {
        return $this->hasMany(CategoryHasService::className(), ['service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceHasPhoto()
    {
        return $this->hasMany(ServiceHasPhoto::className(), ['service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('{{%category_has_service}}', ['service_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (is_string($this->categoryIds)) {
            try {
                $categoryIds = Json::decode($this->categoryIds);
            } catch (Exception $e) {
                return;
            }

            foreach ($categoryIds as $categoryId) {
                $categoryHasService = CategoryHasService::findOne(['service_id' => $this->id, 'category_id' => $categoryId]);
                if (!$categoryHasService) {
                    $categoryHasService = new CategoryHasService();
                    $categoryHasService->category_id = $categoryId;
                    $this->link('categoryHasService', $categoryHasService);
                }
            }

            $categoryHasServices = CategoryHasService::find()
                ->andWhere('service_id = :service_id', [':service_id' => $this->id])
                ->andWhere(['NOT IN', 'category_id', $categoryIds])
                ->all();

            foreach ($categoryHasServices as $categoryHasService) {
                $this->unlink('categoryHasService', $categoryHasService, true);
            }
        }
    }

    public function deletePhoto($photo)
    {
        if ($photo) {
            $serviceHasPhoto = ServiceHasPhoto::findOne([
                'service_id' => $this->id,
                'photo_id' => $photo->id,
            ]);

            if ($serviceHasPhoto) {
                $this->unlink('serviceHasPhoto', $serviceHasPhoto, true);
                return true;
            }
        }

        return false;
    }

    public function getThumbUrl()
    {
        return $this->serviceHasPhoto ? $this->serviceHasPhoto[0]->getThumbUrl() : self::DEFAULT_THUMB;
    }

    public function getImageUrl()
    {
        return $this->serviceHasPhoto ? $this->serviceHasPhoto[0]->getImageUrl() : self::DEFAULT_IMAGE;
    }

    public function getUrl()
    {
        return Url::to(['/service/' . $this->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getCalculatedPrice()
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity->entity;
            $prices = [
                User::ROLE_MEMBER => $this->member_price,
                User::ROLE_PROVIDER => $this->member_price,
                User::ROLE_PARTNER => $this->member_price,
            ];
            if (isset($prices[$user->role])) {
                return $prices[$user->role];
            }
        }

        return $this->price;
    }

    public function getFormattedPrice()
    {
        return Yii::$app->formatter->asCurrency($this->price, 'RUB');
    }

    public function getFormattedMemberPrice()
    {
        return Yii::$app->formatter->asCurrency($this->member_price, 'RUB');
    }

    public function getFormattedCalculatedPrice()
    {
        return Yii::$app->formatter->asCurrency($this->calculatedPrice, 'RUB');
    }
}
