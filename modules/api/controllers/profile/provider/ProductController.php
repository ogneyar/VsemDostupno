<?php

namespace app\modules\api\controllers\profile\provider;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Product;
use app\models\Photo;
use app\modules\api\models\profile\PhotoDeletion;

class ProductController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'updateVisibility' => ['post'],
                    'deletePhoto' => ['post'],
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

        $model = Product::findOne($post['id']);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if (!$model->provider || $model->provider->id != Yii::$app->user->identity->entity->provider->id) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model->visibility = $post['visibility'];

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->save(),
        ];
    }

    public function actionDeletePhoto()
    {
        $photoDeletion = new PhotoDeletion();
        if (!$photoDeletion->load(Yii::$app->request->post()) || !$photoDeletion->validate()) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $product = Product::find()
            ->joinWith(['productHasPhoto'])
            ->where('product_id = :product_id AND photo_id = :photo_id', [
                ':photo_id' => $photoDeletion->key,
                ':product_id' => $photoDeletion->id,
            ])
            ->one();
        if (!$product) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if (!$product->provider || $product->provider->id != Yii::$app->user->identity->entity->provider->id) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $photo = Photo::findOne($photoDeletion->key);
        $className = $photoDeletion->class;
        $model = $className::findOne($photoDeletion->id);
        if (!$photo || !$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success' => $model->deletePhoto($photo),
        ];
    }
}
