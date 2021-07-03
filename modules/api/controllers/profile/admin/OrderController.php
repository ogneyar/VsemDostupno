<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Order;
use app\models\OrderStatus;

class OrderController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'updateStatus' => ['post'],
                ],
            ],
        ]);
    }

    public function actionUpdateStatus()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['orderId']) && isset($post['orderStatusId']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $order = Order::findOne($post['orderId']);
        $orderStatus = OrderStatus::findOne($post['orderStatusId']);
        if (!$order || !$orderStatus) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $order->order_status_id = $orderStatus->id;

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $order->save(),
        ];
    }
}
