<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\db\Query;
use kgladkiy\behaviors\NestedSetBehavior;
use kgladkiy\behaviors\NestedSetQuery;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property integer $visibility
 * @property integer $order
 * @property integer $photo_id
 * @property string $name
 * @property string $description
 * @property integer $root
 * @property integer $left
 * @property integer $right
 * @property integer $level
 * @property string $slug
 * @property string $purchase_timestamp
 * @property string $order_timestamp
 * @property integer $for_reg
 *
 * @property Photo $photo
 * @property CategoryHasProduct[] $categoryHasProduct
 * @property Product[] $products
 * @property CategoryHasService[] $categoryHasService
 * @property Service[] $services
 * @property ProviderHasCategory[] $providerHasCategory
 * @property Provider[] $providers
 * @property array $fancytree
 * @property string $url
 * @property string $fullName
 * @property string $purchaseDate
 * @property string $orderDate
 * @property string $formattedPurchaseDate
 * @property string $formattedOrderDate
 * @property string $htmlFormattedPurchaseDate
 * @property string $htmlFormattedOrderDate
 * @property string $htmlFormattedName
 * @property string $htmlFormattedFullName
 */
class Category extends \yii\db\ActiveRecord
{
    public $parent_node = 0;
    public $purchase_date;
    public $tree_to_save = [];
    
    const MAX_IMAGE_SIZE = 1024;
    const MAX_THUMB_WIDTH = 500;
    const MAX_THUMB_HEIGHT = 500;
    const DEFAULT_IMAGE = '/images/default-image.jpg';
    const DEFAULT_THUMB = '/images/default-thumb.jpg';

    const PURCHASE_SLUG = 'zakupki';
    const FEATURED_SLUG = 'specpredlozheniya';
    const RECENT_SLUG = 'novinki';
    const SERVICE_SLUG = 'uslugi';

    public function behaviors()
    {
        return [
            NestedSetBehavior::className() => [
                'class' => NestedSetBehavior::className(),
                'leftAttribute' => 'left',
                'rightAttribute' => 'right',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new NestedSetQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['visibility', 'order', 'photo_id', 'root', 'left', 'right', 'level', 'for_reg', 'collapsed', 'parent'], 'integer'],
            [['name', 'order'], 'required'],
            [['description', 'external_link'], 'string'],
            [['purchase_timestamp', 'order_timestamp', 'purchaseDate', 'orderDate'], 'safe'],
            [['name', 'slug'], 'string', 'max' => 255]
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
            'order' => 'Порядок',
            'photo_id' => 'Идентификатор изображения',
            'name' => 'Название категории',
            'description' => 'Описание',
            'root' => 'Корень',
            'left' => 'Левый узел',
            'right' => 'Правый узел',
            'level' => 'Уровень',
            'photo' => 'Фотография',
            'thumbUrl' => 'Фотография',
            'slug' => 'Заголовок для URL',
            'purchase_timestamp' => 'Дата и время закупки',
            'order_timestamp' => 'Дата и время последних заказов',
            'purchaseDate' => 'Дата закупки',
            'orderDate' => 'Дата последних заказов',
            'fullName' => 'Название',
            'for_reg' => 'Доступна для регистрации',
            'external_link' => 'Внешняя ссылка для перехода',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deletePhoto($this->photo);

            $categories = self::find()->where(['parent' => $this->id])->all();
            if ($categories) {
                foreach ($categories as $category) {
                    $category->delete();
                    CategoryHasProduct::deleteAll('category_id = :category_id', [':category_id' => $category->id]);
                    CategoryHasService::deleteAll('category_id = :category_id', [':category_id' => $category->id]);
                }
            }
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryHasProduct()
    {
        return $this->hasMany(CategoryHasProduct::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {   
        return $this->hasMany(Product::className(), ['id' => 'product_id'])->viaTable('{{%category_has_product}}', ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryHasService()
    {
        return $this->hasMany(CategoryHasService::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['id' => 'service_id'])->viaTable('category_has_service', ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProviderHasCategory()
    {
        return $this->hasMany(ProviderHasCategory::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProviders()
    {
        return $this->hasMany(Provider::className(), ['id' => 'provider_id'])->viaTable('provider_has_category', ['category_id' => 'id']);
    }

    public function getAllChildrenQuery()
    {
        $tree = self::find()->where(['parent' => $this->id])->all();

        $map = function($items) use (&$map) {
            $results = [];
            foreach ($items as $item) {
                $results[] = $item->id;
                if ($children = self::find()->where(['parent' => $item->id])->all()) {
                    $results = array_merge($results, $map($children));
                }
            }
            return $results;
        };

        return self::find()
            ->where(['IN', 'id', $map($tree)])
            ->andWhere('visibility != 0');
    }

    public function getAllProductsQuery()
    {
        $categoryIds = ArrayHelper::getColumn($this->getAllChildrenQuery()->all(), 'id');
        //$categoryIds = [];
        $categoryIds = array_merge([$this->id], $categoryIds);

        $where = ['>', 'product_feature.quantity', 0];
        if ($this->isPurchase()) {
            $where = [];
        }
        
        $query = new Query();
        $query->select('DISTINCT {{%product}}.id')
            ->from('{{%product}}')
            ->join('LEFT JOIN', '{{%category_has_product}}', '{{%category_has_product}}.product_id = {{%product}}.id')
            ->join('LEFT JOIN', 'product_feature', 'product_feature.product_id = {{%product}}.id')
            ->join('LEFT JOIN', 'product_price', 'product_feature.id = product_price.product_feature_id')
            ->where(['IN', '{{%category_has_product}}.category_id', $categoryIds])
            ->andWhere('product_price.purchase_price IS NOT NULL')
            ->andWhere($where);
        if ($this->isPurchase()) {
            $query->join('RIGHT JOIN', 'purchase_product', 'product_feature.id = purchase_product.product_feature_id');
            $query->andWhere('purchase_product.stop_date >= "' . date('Y-m-d') . '"');
            $query->andWhere(['purchase_product.status' => 'advance']);
            $query->orderBy('purchase_product.purchase_date ASC, {{%product}}.name ASC');
        }
        $productIds = $query->all();
        
        $productIds = ArrayHelper::getColumn($productIds, 'id');

        $ret = Product::find()->where(['IN', 'id', $productIds]);
        if (count($productIds)) $ret->orderBy([new \yii\db\Expression('FIELD (id, ' . implode(',', $productIds) . ')')]);
        
        return $ret;
    }

    public function getAllServicesQuery()
    {
        $categoryIds = ArrayHelper::getColumn($this->getAllChildrenQuery()->all(), 'id');
        //$categoryIds = [];
        $categoryIds = array_merge([$this->id], $categoryIds);

        $query = new Query();
        $serviceIds = $query->select('DISTINCT {{%service}}.id')
            ->from('{{%service}}')
            ->join('LEFT JOIN', '{{%category_has_service}}', '{{%category_has_service}}.service_id = {{%service}}.id')
            ->where(['IN', '{{%category_has_service}}.category_id', $categoryIds])
            ->all();
        $serviceIds = ArrayHelper::getColumn($serviceIds, 'id');

        return Service::find()
            ->where(['IN', 'id', $serviceIds]);
    }

    public static function getFancyTree($selected = [], $tree = [], $visibility = true, $for_reg = false)
    {
        if (!$tree) {
            //$tree = Category::find()->roots()->all();
            $tree = self::find()->where(['parent' => 0])->orderBy('name')->all();
        }

        $map = function($items) use (&$map, &$selected, &$visibility, &$for_reg) {
            $results = [];
            foreach ($items as $item) {
                if ($visibility || $item->visibility) {
                    if ($for_reg) {
                        if ($item->for_reg) {
                            $node = [
                                'title' => $item->name,
                                'key' => $item->id,
                                'selected' => in_array($item->id, $selected),
                            ];
                            //$children = $item->children()->all();
                            $children = self::find()->where(['parent' => $item->id])->orderBy('name')->all();
                            if ($children) {
                                $node += [
                                    'folder' => true,
                                    'children' => $map($children),
                                ];
                            }
                            $results[] = $node;
                        }
                    } else {
                        $node = [
                            'title' => $item->name,
                            'key' => $item->id,
                            'selected' => in_array($item->id, $selected),
                        ];
                        //$children = $item->children()->all();
                        $children = self::find()->where(['parent' => $item->id])->orderBy('name')->all();
                        if ($children) {
                            $node += [
                                'folder' => true,
                                'children' => $map($children),
                            ];
                        }
                        $results[] = $node;
                    }
                }
            }
            return $results;
        };

        return $map($tree);
    }
    
    public static function getSelectTree($category = false)
    {
        $tree = Category::find()->tree($category);

        $results = [];
        $map = function($items, $indent) use (&$map, &$results) {
            foreach ($items as $item) {
                if (!$indent) {
                    $results[$item['id']] = Html::encode($item['name']);
                } else {
                    $results[$item['id']] = preg_replace('/\s/', '&nbsp;', $indent) . '&#8735;' . Html::encode($item['name']);
                }
                if ($item['children']) {
                    $map($item['children'], '   ' . $indent);
                }
            }
            return $results;
        };

        return $map($tree, '');
    }

    public function deletePhoto($photo)
    {
        if ($photo && $photo->id == $this->photo_id) {
            $this->photo_id = null;
            $this->saveNode();
            $photo->delete();

            return true;
        }

        return false;
    }

    public function getImageUrl()
    {
        return $this->photo ? $this->photo->imageUrl : self::DEFAULT_IMAGE;
    }

    public function getThumbUrl()
    {
        return $this->photo ? $this->photo->thumbUrl : self::DEFAULT_THUMB;
    }

    public function getBreadcrumbs($name = null)
    {
        $breadcrumbs = [];

        $category = $this;
        
        do {
            if ($category->parent == 0) {
                
                break;
            }
            array_unshift($breadcrumbs, [
                'label' => Html::encode($category->fullName),
                'url' => $category->url,
            ]);
            $category = self::findOne($category->parent);
        } while ($category);
        
        if ($name) {
            /*$breadcrumbs[] = [
                'label' => Html::encode($this->fullName),
                'url' => $this->url,
            ];*/
            $breadcrumbs[] = Html::encode($name);
        } else {
            $breadcrumbs[] = Html::encode($this->fullName);
        }

        return $breadcrumbs;
    }

    public function getUrl()
    {
        return Url::to(['/category/' . ($this->slug ? $this->slug : $this->id)]);
    }

    public function getPurchaseDate()
    {
        if (strtotime($this->purchase_timestamp) > 0) {
            return mb_substr($this->purchase_timestamp, 0, 10, Yii::$app->charset);
        }

        return '';
    }

    public function setPurchaseDate($value)
    {
        $this->purchase_timestamp = $value ? $value : 0;
    }

    public function getOrderDate()
    {
        if (strtotime($this->order_timestamp) > 0) {
            return mb_substr($this->order_timestamp, 0, 10, Yii::$app->charset);
        }

        return '';
    }

    public function setOrderDate($value)
    {
        $this->order_timestamp = $value ? $value : 0;
    }

    public function getFormattedPurchaseDate()
    {
        if (strtotime($this->purchase_timestamp) > 0) {
            return Yii::$app->formatter->asDate($this->purchase_timestamp, 'long');
        }

        return '';
    }

    public function getFormattedOrderDate()
    {
        if (strtotime($this->order_timestamp) > 0) {
            return Yii::$app->formatter->asDate($this->order_timestamp, 'long');
        }

        return '';
    }

    public function getHtmlFormattedPurchaseDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedPurchaseDate);
    }

    public function getHtmlFormattedOrderDate()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->formattedOrderDate);
    }

    public function getHtmlFormattedName()
    {
        return preg_replace('/\s+/', '&nbsp;', $this->name);
    }

    public function getFullName()
    {
        if ($this->formattedPurchaseDate) {
            return /*$this->formattedPurchaseDate . ' ' . */$this->name;
        }

        return $this->name;
    }

    public function getHtmlFormattedFullName()
    {
        return Html::encode($this->fullName);
    }

    public function getRootParent()
    {
        $category = $this;
        
        do {
            if ($category->parent == 0) {
                return $category;
            }
            $category = self::findOne($category->parent);
        } while ($category);

        return false;
    }
    
    public function isPurchase()
    {
        $category = $this;

        do {
            if ($category->slug == self::PURCHASE_SLUG) {
                return true;
            }
            $category = self::findOne($category->parent);
        } while ($category);

        return false;
    }
    
    public function isStock()
    {
        $category = $this;

        do {
            if ($category->id == '220') {
                return true;
            }
            $category = self::findOne($category->parent);
        } while ($category);

        return false;
    }
    
    public function isRecomended()
    {
        $category = $this;

        do {
            if ($category->id == '234') {
                return true;
            }
            $category = self::findOne($category->parent);
        } while ($category);

        return false;
    }
    
    public static function getCategoryPath($id)
    {
        $category = self::findOne($id);
        if ($category) {
            $path = '';
            $category_bc = $category->getBreadcrumbs($category->name);
            //print_r($category_bc);
            foreach ($category_bc as $val) {
                if (isset($val['label'])) {
                    $path .= $val['label'] . ' / ';
                }
            }
            return $path;
        }
        return false;
    }
    
    public static function getMenuItems($data)
    {
        $ret = [];
        $categories = self::find()->where(['parent' => $data->id])->andWhere('visibility != 0')->orderBy(['name' => SORT_ASC])->all();
            
        if ($categories) {
            foreach ($categories as $category) {
                $ret[] = [
                    'content' => $category->htmlFormattedFullName,
                    'url' => !empty($category->external_link) ? $category->external_link : $category->url,
                    'options' => [
                        'target' => !empty($category->external_link) ? '_blank' : '_self',
                    ],
                    'thumbUrl' => $category->thumbUrl,
                    'model' => $category
                ];
            }
        }
        
        return $ret;
    }
    
    public static function getFancyCategories()
    {
        $tree = self::find()->where(['parent' => 0])->orderBy('name')->all();
        $map = function($items) use (&$map) {
            $results = [];
            foreach ($items as $item) {
                $node = [
                    'title' => $item->name,
                    'id' => $item->id,
                ];
                $children = self::find()->where(['parent' => $item->id])->orderBy('name')->all();
                if ($children) {
                    $node += [
                        'folder' => true,
                        'expanded' => !$item->collapsed,
                        'children' => $map($children),
                    ];
                }
                $results[] = $node;
            }
            return $results;
        };

        
        return $map($tree);
    }
    
    public function setFancyCategories($data)
    {
        foreach ($data as $val) {
            $this->tree_to_save[] = ['id' => $val->data->id, 'parent' => 0];
        }
        $map = function($items) use (&$map) {
            foreach ($items as $item) {
                $this->tree_to_save[] = ['id' => $item->data->id, 'parent' => $this->parent_node];
                if (isset($item->children)) {
                    $this->parent_node = $item->data->id;
                    $map($item->children);
                }
            }
            return true;
        };
        $res = $map($data);
        
        /*if (count($this->tree_to_save)) {
            $saved = [];
            foreach ($this->tree_to_save as $save) {
                if (!in_array($save['id'], $saved)) {
                    $cat = self::findOne($save['id']);
                    $cat->parent = $save['parent'];
                    $cat->save();
                    $saved[] = $save['id'];
                }
            }
        }*/
    }
}
