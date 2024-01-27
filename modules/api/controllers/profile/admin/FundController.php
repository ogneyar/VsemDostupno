<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Fund;

class FundController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'updateVisibilityButtons' => ['post'],
                ],
            ],
        ]);
    }

    public function actionUpdateVisibilityButtons()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['id']) && isset($post['visibility']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = Fund::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $model->visibility_buttons = $post['visibility'];

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->save(),
        ];
    }

}
