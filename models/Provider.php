<?php

namespace app\models;

use Yii;
use yii\base\UnknownPropertyException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\models\User;

/**
 * This is the model class for table "provider".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $field_of_activity
 * @property string $offered_goods
 * @property string $legal_address
 * @property string $snils
 * @property string $ogrn
 * @property string $site
 * @property string $description
 *
 * @property User $user
 * @property ProviderHasCategory[] $providerHasCategories
 * @property Category[] $categories
 * @property ProviderHasProduct[] $providerHasProducts
 * @property Product[] $products
 * @property ProviderNotification[] $providerNotifications
 * @property StockHead[] $stockHeads
 */
class Provider extends \yii\db\ActiveRecord
{
    public $categoryIds;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'provider';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name'], 'required'],
            [['categoryIds'], 'required', 'except' => ['self_reg', 'become_provider']],
            [['field_of_activity', 'legal_address', 'snils', 'ogrn'], 'required', 'except' => 'become_provider'],
            [['user_id'], 'integer'],
            [['field_of_activity', 'description'], 'string'],
            [['name', 'legal_address'], 'string', 'max' => 255],
            [['snils'], 'string', 'max' => 11],
            [['ogrn'], 'string', 'max' => 13],
            [['site'], 'string', 'max' => 100],
            [['user_id'], 'unique'],
            [['name'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $user = new User();

        return ArrayHelper::merge([
                'id' => 'Идентификатор',
                'user_id' => 'Идентификатор пользователя',
                'name' => 'Название организации',
                'categoryIds' => 'Категории',
                'field_of_activity' => 'Сфера деятельности организации по ОКВЕД',
                'offered_goods' => 'Наименования предлагаемых товаров',
                'legal_address' => 'Юридический адрес',
                'snils' => 'СНИЛС',
                'ogrn' => 'ОГРН',
                'site' => 'Сайт компании',
                'description' => 'Описание предложений',
            ],
            $user->attributeLabels()
        );
    }

    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $e) {
            if ($this->user) {
                return $this->user->$name;
            }
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProviderHasProduct()
    {
        return $this->hasMany(ProviderHasProduct::className(), ['provider_id' => 'id']);
    }

    public function getProviderStock()
    {
        return $this->hasMany(StockHead::className(), ['provider_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'product_id'])->viaTable('provider_has_product', ['provider_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProviderHasCategory()
    {
        return $this->hasMany(ProviderHasCategory::className(), ['provider_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('provider_has_category', ['provider_id' => 'id']);
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
                $providerHasCategory = ProviderHasCategory::findOne(['provider_id' => $this->id, 'category_id' => $categoryId]);
                if (!$providerHasCategory) {
                    $providerHasCategory = new ProviderHasCategory();
                    $providerHasCategory->category_id = $categoryId;
                    $this->link('providerHasCategory', $providerHasCategory);
                }
            }

            $providerHasCategories = ProviderHasCategory::find()
                ->andWhere('provider_id = :provider_id', [':provider_id' => $this->id])
                ->andWhere(['NOT IN', 'category_id', $categoryIds])
                ->all();

            foreach ($providerHasCategories as $providerHasCategory) {
                $this->unlink('providerHasCategory', $providerHasCategory, true);
            }
        }
    }
}
