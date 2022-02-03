<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\Cart;
use app\models\Category;


use app\modules\purchase\models\PurchaseProduct;

class ProductController extends BaseController
{
    public function actionIndex($id)
    {
        $model = Product::find()
            ->joinWith('productFeatures')
            ->joinWith('productFeatures.productPrices')
            ->joinWith('categoryHasProduct')
            ->joinWith('categoryHasProduct.category')
            ->andWhere('product.id = :id', [':id' => $id])
            ->andWhere('product.visibility != 0')
            ->andWhere('published != 0')
            ->one();

        if (!$model->isPurchase()) {
            $model = Product::find()
                ->joinWith('productFeatures')
                ->joinWith('productFeatures.productPrices')
                ->andWhere('product.id = :id', [':id' => $id])
                ->andWhere('product.visibility != 0')
                ->andWhere('published != 0')
                ->andWhere('product_feature.quantity > 0')
                ->one(); 
        } else {
            $model = Product::find()
                ->joinWith('productFeatures')
                ->joinWith('productFeatures.productPrices')
                ->joinWith('productFeatures.purchaseProducts')
                ->andWhere('product.id = :id', [':id' => $id])
                ->andWhere('product.visibility != 0')
                ->andWhere('published != 0')
                ->one();
        }
        
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
        
        return $this->render('index', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }
    
    public function actionGetPrices()
    {
        $feature_id = $_POST['f_id'];
        return $this->renderPartial('_prices', [
            'all_price' => Product::getFormattedPriceFeature($feature_id),
            'member_price' => Product::getFormattedMemberPriceFeature($feature_id),
        ]);
    }
    
    public function actionInCart()
    {
        $feature_id = $_POST['f_id'];
        $feature = ProductFeature::findOne($feature_id);
        return Cart::hasProductId($feature);
    }
    
    public function actionGetPurchaseDate()
    {
        $feature_id = $_POST['f_id'];
        $url = $_POST['url'];
        $product = PurchaseProduct::getPurchaseDateByFeature($feature_id);
        if ($product) {
            return $this->renderPartial('_dates', [
                'purchase_date' => $product[0]->htmlFormattedPurchaseDate,
                'stop_date' => $product[0]->htmlFormattedStopDate,
                'url' => $url,
            ]);
        } else {
            return $this->renderPartial('_dates', [
                'no_dates' => true
            ]);
        }
        
    }
}
