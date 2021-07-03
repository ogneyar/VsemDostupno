<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Photo;
use app\models\Product;
use app\modules\api\models\profile\PhotoDeletion;

class PhotoController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    public function actionDelete()
    {
        $photoDeletion = new PhotoDeletion();
        if (!$photoDeletion->load(Yii::$app->request->post()) || !$photoDeletion->validate()) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $photo = Photo::findOne($photoDeletion->key);
        $className = $photoDeletion->class;
        $model = $className::findOne($photoDeletion->id);
        
        
        if (!$photo || !$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }
        
        if ($className == Product::className()) {
            $manufacturer = $photoDeletion->manufacturer;
            $res = $model->deletePhoto($photo, $manufacturer);
        } else {
            $res = $model->deletePhoto($photo);
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $res,
        ];
    }
}
