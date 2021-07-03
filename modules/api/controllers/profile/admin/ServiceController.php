<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Service;

class ServiceController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'updateVisibility' => ['post'],
                    'updatePublished' => ['post'],
                ],
            ],
        ]);
    }

    public function actionUpdateVisibility()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['id']) && isset($post['visibility']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = Service::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $model->visibility = $post['visibility'];

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->save(),
        ];
    }

    public function actionUpdatePublished()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['id']) && isset($post['published']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = Service::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $model->published = $post['published'];

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->save(),
        ];
    }
}
