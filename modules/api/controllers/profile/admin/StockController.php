<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\modules\api\models\profile\admin\StockAddition;
use app\models\User;
use app\models\StockBody;
use app\models\Product;

class StockController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'updateVisibility' => ['post'],
                    'updatePublished' => ['post'],
                    'search' => ['get'],
                    'add' => ['post'],
                ],
            ],
        ]);
    }

    public function actionAdd()
    {
        $stockAddition = new StockAddition();
        if (!$stockAddition->load(Yii::$app->request->post())) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        if(!$stockAddition->validate()) {
            print_r($stockAddition->errors);
            die();
        }

        if (!empty($stockAddition->product_id)) {
            $product_id = $stockAddition->product_id;
        } else if (!empty($stockAddition->product_name)) {
            $product = new Product;
            $product->name = $stockAddition->product_name;
            $product->description = $stockAddition->product_name;
            $product->price = $stockAddition->summ;
            $product->member_price = $stockAddition->summ;
            $product->partner_price = $stockAddition->summ;
            $product->purchase_price = $stockAddition->summ;
            $product->storage_price = $stockAddition->summ;
            $product->inventory = $stockAddition->count;
            $product->save();
            
            $product_id = $product->id;
        }
        $product = Product::findOne($product_id);
        $product_name = $product->name;

        if (!$product || !$product->visibility) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

//        $quantity = $product->inventory && $product->inventory < $stockAddition->quantity ?
//            $product->inventory : $stockddition->quantity;
        $total = sprintf('%.2f', $stockAddition->count * $stockAddition->summ);

        return [
            'product_id' => $product_id,
            'name' => $product_name,
            'tare' => $stockAddition->tare,
            'weight' => $stockAddition->weight,
            'measurement' => $stockAddition->measurement,
            'count' => $stockAddition->count,
            'summ' => $stockAddition->summ,
            'total_summ' => $total,
            'deposit' => $stockAddition->deposit,
            'comment' => $stockAddition->comment
        ];
    }

    public function actionUpdateDeposit()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['id']) && isset($post['visibility']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = StockBody::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $model->deposit = $post['visibility'];

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->save(),
        ];
    }
}