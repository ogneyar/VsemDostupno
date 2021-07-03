<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Product;
use app\models\ProductNewPrice;
use app\models\ProductFeature;
use app\models\ProductPrice;

class ApplyNewPriceController extends Controller
{
    public function actionIndex()
    {
        $products = ProductNewPrice::getProducts();
        if ($products) {
            foreach ($products as $product) {
                $model_product_feature = ProductFeature::findOne($product->product_feature_id);
                $model_product_feature->quantity = $product->quantity;
                if ($model_product_feature->save()) {
                    $model_product_price = ProductPrice::find()->where(['product_feature_id' => $product->product_feature_id])->one();
                    $model_product_price->purchase_price = $product->price;
                    if ($model_product_price->save()) {
                        $product->delete();
                    }
                }
            }
        }
    }
}