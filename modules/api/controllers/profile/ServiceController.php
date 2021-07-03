<?php

namespace app\modules\api\controllers\profile;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Exception;
use app\models\User;
use app\models\Service;
use app\models\Photo;
use app\modules\api\models\profile\PhotoDeletion;

class ServiceController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (Yii::$app->user->identity->entity->disabled ||
                            in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            return true;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionUpdateVisibility()
    {
        $post = Yii::$app->request->post();
        if (!(isset($post['id']) && isset($post['visibility']))) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = Service::find()
            ->where('id = :id AND user_id = :user_id', [':id' => $post['id'], ':user_id' => Yii::$app->user->identity->id])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
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

        $service = Service::find()
            ->joinWith(['serviceHasPhoto'])
            ->where('service_id = :service_id AND photo_id = :photo_id', [
                ':photo_id' => $photoDeletion->key,
                ':service_id' => $photoDeletion->id,
            ])
            ->one();
        if (!$service) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if ($service->user_id != Yii::$app->user->identity->id) {
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
