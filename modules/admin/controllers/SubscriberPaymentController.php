<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\SubscriberPayment;

/**
 * SubscriberPaymentController implements the CRUD actions for Page model.
 */
class SubscriberPaymentController extends BaseController
{
    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SubscriberPayment::find(),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
