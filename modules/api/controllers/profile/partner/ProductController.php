<?php

namespace app\modules\api\controllers\profile\partner;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\modules\api\models\profile\admin\ProductAddition;
use app\models\User;
use app\models\Product;
use app\models\ProductFeature;
use app\modules\purchase\models\PurchaseProduct;

class ProductController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'search' => ['get'],
                    'add' => ['post'],
                ],
            ],
        ]);
    }

    public function actionSearch($q = null, $id = null, $c = null)
    {
        $out = [
            'results' => [
                [
                    'id' => '',
                    'text' => '',
                ],
            ],
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!is_null($q)) {
            if (!is_null($c) && $c == 'false') {
                $productQuery = ProductFeature::find()
                    ->joinWith('product')
                    ->orWhere('name like :q', [':q' => '%' . $q . '%'])
                    ->andWhere('quantity != 0')
                    ->andWhere('visibility != 0')
                    ->orderBy(['name' => SORT_ASC]);
                
                $data = [];
                foreach ($productQuery->each() as $product) {
                    if ($product->quantity) {
                        $text = sprintf('%s (%s)', $product->product->name . ', ' . $product->featureName, $product->is_weights == 1 ? floor($product->quantity / $product->volume) : number_format($product->quantity));
                    } else {
                        $text = $product->product->name;
                    }
                    $data[] = [
                        'id' => $product->id,
                        'text' => $text,
                    ];
                }

                if ($data) {
                    $out['results'] = $data;
                }
            } elseif (!is_null($c) && $c == 'true') {
                $productQuery = PurchaseProduct::find()
                    ->joinWith('productFeature')
                    ->joinWith('productFeature.product')
                    ->orWhere('name like :q', [':q' => '%' . $q . '%'])
                    ->andWhere('visibility != 0')
                    ->andWhere('purchase_date > NOW()')
                    ->orderBy(['purchase_date' => SORT_ASC]);
                
                $data = [];
                foreach ($productQuery->each() as $product) {
                    $text = sprintf('%s (%s)', $product->productFeature->product->name . ', ' . $product->productFeature->featureName, (new \DateTime($product->purchase_date))->format('d.m.Y'));
                    
                    $data[] = [
                        'id' => $product->id,
                        'text' => $text,
                    ];
                }

                if ($data) {
                    $out['results'] = $data;
                }
            }
        } elseif ($id > 0) {
            $productQuery = ProductFeature::find()
                ->joinWith('product')
                ->andWhere('product.id = :id', [':id' => $id])
                ->andWhere('quantity != 0')
                ->andWhere('visibility != 0')
                ->one();
            
            if ($product) {
                if ($product->quantity) {
                    $text = sprintf('%s (%s)', $product->product->name . ', ' . $product->featureName, $product->is_weights == 1 ? floor($product->quantity / $product->volume) : number_format($product->quantity));
                } else {
                    $text = $product->product->name;
                }
                $out['results'] = [
                    [
                        'id' => $product->id,
                        'text' => $text,
                    ],
                ];
            }
        }

        return $out;
    }

    public function actionAdd()
    {
        $productAddition = new ProductAddition();
        if (!$productAddition->load(Yii::$app->request->post()) || !$productAddition->validate()) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $user = User::findOne($productAddition->user_id);
        //$product = Product::find()->andWhere('name LIKE :q',[':q'=>'%'.$productAddition->product_id.'%'])->one();
        if ($productAddition->is_purchase == 'true') {
            $product = PurchaseProduct::find()
                ->joinWith('productFeature')
                ->joinWith('productFeature.product')
                ->joinWith('productFeature.productPrices')
                ->where(['purchase_product.id' => $productAddition->product_id])
                ->one();
        } else {
            $product = ProductFeature::find()
                ->joinWith('product')
                ->joinWith('productPrices')
                ->where(['product_feature.id' => $productAddition->product_id])
                ->one();
        }

        if (!$user || $user->disabled || !$product) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($productAddition->is_purchase == 'true') {
            $purchase_date = (new \DateTime($product->purchase_date))->format('d.m.Y');
            $quantity = $productAddition->quantity;
            $price = $product->is_weights == 1 ? $product->productFeature->productPrices[0]->member_price * $product->productFeature->volume : $product->productFeature->productPrices[0]->member_price;
            $product_id = $product->id;
            $product_name = $product->productFeature->product->name . ', ' . $product->productFeature->featureName;
        } else {
            $f_quantity = $product->is_weights == 1 ? floor($product->quantity / $product->volume) : number_format($product->quantity);
            $quantity = $product->quantity && $f_quantity < $productAddition->quantity ? $f_quantity : $productAddition->quantity;
            $price = $product->is_weights == 1 ? $product->productPrices[0]->member_price * $product->volume : $product->productPrices[0]->member_price;
            $purchase_date = "";
            $product_id = $product->id;
            $product_name = $product->product->name . ', ' . $product->featureName;
        }
        
        $total = sprintf('%.2f', $quantity * $price);

        return [
            'id' => $product_id,
            'name' => $product_name,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total,
            'purchase_date' => $purchase_date,
        ];
    }
}
