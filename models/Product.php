<?php

namespace app\models;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\base\Exception;
use app\models\User;
use app\models\Provider;
use app\models\ProductFeature;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property integer $visibility
 * @property string $name
 * @property string $description
 * @property string $composition
 * @property string $packing
 * @property string $manufacturer
 * @property string $status
 * @property integer $published
 * @property integer $only_member_purchase
 * @property string $expiry_timestamp
 * @property integer $min_inventory
 * @property integer $auto_send
 * @property integer $manufacturer_photo_id
 *
 * @property CategoryHasProduct[] $categoryHasProduct
 * @property ProductHasPhoto[] $productHasPhoto
 * @property string $categoryIds
 * @property Category[] $categories
 * @property string $purchaseCategories
 * @property string $purchaseCategory
 * @property string $orderDate
 * @property string $formattedOrderDate
 * @property string $htmlFormattedOrderDate
 * @property string $purchaseDate
 * @property string $formattedPurchaseDate
 * @property string $htmlFormattedPurchaseDate
 * @property string $url
 * @property integer $currentInventory
 * @property Provider $provider
 * @property Category[] $providerCategories
 */
class Product extends \yii\db\ActiveRecord
{
    public $category_id;
    public $provider_id;
    public $categories = [];
    public $gallery; /* dummy property */
    public $quantity = 1;

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
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['visibility', 'only_member_purchase', 'min_inventory', 'auto_send', 'manufacturer_photo_id'], 'integer'],
            [['name', 'description'], 'required'],
            [['category_id', 'provider_id'], 'required', 'except' => ['apply_product', 'order_product']],
            [['description', 'composition', 'packing', 'manufacturer', 'status'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['gallery', 'quantity', 'expiry_timestamp', 'stock_date'], 'safe'],
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
            'name' => 'Название',
            'description' => 'Описание',
            'composition' => 'Состав',
            'packing' => 'Фасовка',
            'manufacturer' => 'Производитель',
            'status' => 'Статус продукта',
            'published' => 'Опубликованный',
            'only_member_purchase' => 'Товар для участников',
            'expiry_timestamp' => 'Срок годности',
            'min_inventory' => 'Минимальный запас',
            'category_id' => 'Категория',
            'gallery' => 'Фотографии',
            'thumbUrl' => 'Фотография',
            'quantity' => 'Количество',
            'auto_send' => 'Отправление авто заявки поставщику',
            'manufacturer_photo_id' => 'Фото производителя',
            'photo' => 'Фото производителя',
            'thumbUrlManufacturer' => 'Фото производителя',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            foreach ($this->productHasPhoto as $productHasPhoto) {
                $productHasPhoto->delete();
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$this->expiry_timestamp) {
                $this->expiry_timestamp = '0000-00-00 00:00:00';
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->isNewRecord) {
            if ($this->category_id != 0) {
                $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $this->id, 'category_id' => $this->category_id]);
                if (!$categoryHasProduct) {
                    $categoryHasProduct = new CategoryHasProduct();
                    $categoryHasProduct->category_id = $this->category_id;
                    $categoryHasProduct->product_id = $this->id;
                    $categoryHasProduct->save();
                }
            }
            
            $providerHasProduct = ProviderHasProduct::findOne(['product_id' => $this->id, 'provider_id' => $this->provider_id]);
            if (!$providerHasProduct) {
                $providerHasProduct = new ProviderHasProduct();
                $providerHasProduct->provider_id = $this->provider_id;
                $providerHasProduct->product_id = $this->id;
                $providerHasProduct->save();
            }
        } else {
            if ($this->scenario != 'apply_product') {
                if ($this->category_id != 0) {
                    $categoryForDel = CategoryHasProduct::findAll(['product_id' => $this->id]);
                    if ($categoryForDel) {
                        foreach ($categoryForDel as $cat) {
                            $cat->delete();
                        }
                    }
                    $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $this->id, 'category_id' => $this->category_id]);
                    if (!$categoryHasProduct) {
                        $categoryHasProduct = new CategoryHasProduct();
                        $categoryHasProduct->category_id = $this->category_id;
                        $categoryHasProduct->product_id = $this->id;
                        $categoryHasProduct->save();
                    }
                }
                
                if ($this->provider_id != 0) {
                    $providerForDel = ProviderHasProduct::findAll(['product_id' => $this->id]);
                    if ($providerForDel) {
                        foreach ($providerForDel as $prov) {
                            $prov->delete();
                        }
                    }
                    
                    $providerHasProduct = new ProviderHasProduct();
                    $providerHasProduct->provider_id = $this->provider_id;
                    $providerHasProduct->product_id = $this->id;
                    $providerHasProduct->save();
                }
            }
        }
    }

    public function getPhoto()
    {
        return $this->hasOne(Photo::className(), ['id' => 'manufacturer_photo_id']);
    }
    
    public function getCategoryHasProduct()
    {
        return $this->hasMany(CategoryHasProduct::className(), ['product_id' => 'id']);
    }
    
    public function getProviderHasProduct()
    {
        return $this->hasMany(ProviderHasProduct::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductHasPhoto()
    {
        return $this->hasMany(ProductHasPhoto::className(), ['product_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductFeatures()
    {
        return $this->hasMany(ProductFeature::className(), ['product_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductPrices()
    {
        return $this->hasMany(ProductPrice::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->viaTable('{{%category_has_product}}', ['product_id' => 'id']);
    }
    
    public function getProvider()
    {
        return $this->hasOne(Provider::className(), ['id' => 'provider_id'])->viaTable('{{%provider_has_product}}', ['product_id' => 'id']);
    }

    public function deletePhoto($photo, $manufacturer = 0)
    {
        if ($photo) {
            if ($manufacturer == 0) {
                $productHasPhoto = ProductHasPhoto::findOne([
                    'product_id' => $this->id,
                    'photo_id' => $photo->id,
                ]);

                if ($productHasPhoto) {
                    $this->unlink('productHasPhoto', $productHasPhoto, true);
                    return true;
                }
            } else {
                $this->manufacturer_photo_id = null;
                $this->save();
                $photo->delete();
                return true;
            }
        }

        return false;
    }

    public function getThumbUrl()
    {
        return $this->productHasPhoto ? $this->productHasPhoto[0]->getThumbUrl() : self::DEFAULT_THUMB;
    }
    
    public function getThumbUrlManufacturer()
    {
        return $this->photo ? $this->photo->getThumbUrl() : self::DEFAULT_THUMB;
    }

    public function getImageUrl()
    {
        return $this->productHasPhoto ? $this->productHasPhoto[0]->getImageUrl() : self::DEFAULT_IMAGE;
    }
    
    public function getImageUrlManufacturer()
    {
        return $this->photo ? $this->photo->getImageUrl() : self::DEFAULT_IMAGE;
    }

    public function isPurchase()
    {
        if ($this->category->isPurchase()) {
            return true;
        }
        
        return false;
    }

    public function getPurchaseCategories()
    {
        $categories = [];
        if ($this->category->isPurchase() && $this->category->formattedPurchaseDate) {
            $categories[$this->category->orderDate] = $this->category;
        }

        ksort($categories);

        return array_values($categories);
    }

    public function getPurchaseCategory()
    {
        $categories = $this->purchaseCategories;

        return $categories ? $categories[0] : null;
    }

    public function getOrderDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->orderDate : '';
    }

    public function getFormattedOrderDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->formattedOrderDate : '';
    }

    public function getHtmlFormattedOrderDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->htmlFormattedOrderDate : '';
    }

    public function getPurchaseDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->purchaseDate : '';
    }

    public function getFormattedPurchaseDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->formattedPurchaseDate : '';
    }

    public function getHtmlFormattedPurchaseDate()
    {
        return $this->purchaseCategory ? $this->purchaseCategory->htmlFormattedPurchaseDate : '';
    }

    public function getUrl()
    {
        return Url::to(['/product/' . $this->id]);
    }

    public function getProviderCategories()
    {
        if ($this->provider && $this->provider->providerHasCategory) {
            return Category::find()
                ->joinWith(['providerHasCategory'])
                ->where('provider_id = :provider_id', [':provider_id' => $this->provider->id])
                ->all();
        }

        return [];
    }

    public function getStock_body()
    {
        return $this->hasOne(StockBody::className(),['product_id'=>'id']);
    }
    
    public static function getProductModelById($id)
    {
        return self::find()->where(['id' => $id])->with('category', 'provider', 'provider.user', 'productFeatures', 'productFeatures.productPrices')->one();
    }
    
    public static function getProductsByProvider($provider_id)
    {
        $query = self::find();
        $query->joinWith('categoryHasProduct');
        $query->joinWith('categoryHasProduct.category');
        $query->joinWith('providerHasProduct')->where(['provider_has_product.provider_id' => $provider_id])->orderBy('category_has_product.category_id');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10000,
            ],
        ]);
        
        return $dataProvider;
    }
    
    public static function getProductsByProviderAll($provider_id)
    {
        $query = self::find();
        $query->joinWith('categoryHasProduct');
        $query->joinWith('categoryHasProduct.category');
        $query->joinWith('providerHasProduct')->where(['provider_has_product.provider_id' => $provider_id])->orderBy('category_has_product.category_id');
        $ret = $query->all();
        
        
        return $ret;
    }
    
    public static function getProductsByProviderView($provider_id)
    {
        $query = ProductFeature::find();
        $query->joinWith('product');
        $query->joinWith('product.categoryHasProduct');
        $query->joinWith('product.categoryHasProduct.category');
        $query->joinWith('product.providerHasProduct')->where(['provider_has_product.provider_id' => $provider_id])->orderBy('product.id');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10000,
            ],
        ]);
        
        return $dataProvider;
    }
    
    public function getProviderForView()
    {
        return isset($this->provider) ? ($this->provider->name . ' / ' . $this->provider->user->fullName) : '';
    }
    
    public function getCategoryForView()
    {
        return isset($this->category) ? $this->category->name : '';
    }
    
    public static function getPriceList()
    {
        return ProductFeature::find()
            ->joinWith('productPrices')
            ->joinWith('product')
            ->joinWith('product.categoryHasProduct')
            ->joinWith('product.categoryHasProduct.category')
            ->andWhere('product.visibility != 0')
            ->andWhere('product.published != 0')
            ->andWhere('category.visibility != 0')
            ->andWhere('quantity > 0')
            ->all();
        
        
        //return self::find()
//            ->select('product.*, category.*')
//            ->joinWith('categoryHasProduct')
//            ->joinWith('categoryHasProduct.category')
//            ->where(['product.visibility' => 1, 'product.published' => 1])
//            ->andWhere(['<>', 'product.inventory', 0])
//            ->all();
    }
    
    public function getFeaturesForView()
    {
        $ret = '';
        $res = ProductFeature::find()->with('productPrices')->where(['product_id' => $this->id])->all();
        if ($res) {
            foreach ($res as $item) {
                $ret .= '<b>' . $item->tare . ', ' . $item->volume . ' ' . $item->measurement . '</b> - <b>' . $item->quantity . '</b> шт., закупочная цена - <b>' . $item->productPrices[0]->purchase_price . '</b> руб., цена для участников - <b>' . $item->productPrices[0]->member_price . '</b> руб., цена для всех - <b>' . $item->productPrices[0]->price . '</b> руб. <br />';
            }
        }
        return $ret;
    }
    
    public function getFormattedMemberPrice()
    {
        $product_price = ProductPrice::find()->where(['product_id' => $this->id])->orderBy('product_feature_id')->limit(1)->all();
        if ($product_price) {
            return Yii::$app->formatter->asCurrency($product_price[0]->member_price, 'RUB');
        }
    }
    
    public function getFormattedPrice()
    {
        $product_price = ProductPrice::find()->where(['product_id' => $this->id])->orderBy('product_feature_id')->limit(1)->all();
        if ($product_price) {
            return Yii::$app->formatter->asCurrency($product_price[0]->price, 'RUB');
        }
    }
    
    public static function getFormattedMemberPriceFeature($feature_id)
    {
        $feature = ProductPrice::find()->where(['product_feature_id' => $feature_id])->one();
        return Yii::$app->formatter->asCurrency($feature->member_price, 'RUB');
    }
    
    public static function getFormattedPriceFeature($feature_id)
    {
        $feature = ProductPrice::find()->where(['product_feature_id' => $feature_id])->one();
        return Yii::$app->formatter->asCurrency($feature->price, 'RUB');
    }
    
    public static function getNextId()
    {
        $sql = "SHOW TABLE STATUS LIKE '" . self::tableName() . "'";
        $res = Yii::$app->db->createCommand($sql)->queryOne();
        return $res['Auto_increment'];
    }
}
