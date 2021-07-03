<?php

namespace app\modules\site\controllers\profile;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use app\models\Email;
use app\modules\site\controllers\BaseController;
use app\models\User;
use app\models\Service;
use app\models\ProviderHasProduct;
use app\models\Photo;
use app\models\ServiceHasPhoto;
use app\models\NoticeEmail;

class ServiceController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index',
                            'create',
                            'update',
                            'delete',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
                            }

                            if (Yii::$app->user->identity->entity->disabled) {
                                $action->controller->redirect('/profile/logout')->send();
                                exit();
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $query = Service::find()
            ->where('user_id = :user_id', [':user_id' => $this->identity->entity->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Service([
            'user_id' => $this->identity->entity->id,
            'visibility' => 1,
            'published' => 1,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save();

            $gallery = UploadedFile::getInstances($model, 'gallery');
            foreach ($gallery as $file) {
                $photo = Photo::createPhoto(
                    Service::MAX_GALLERY_IMAGE_SIZE,
                    Service::MAX_GALLERY_THUMB_WIDTH,
                    Service::MAX_GALLERY_THUMB_HEIGHT,
                    $file->tempName
                );
                $serviceHasPhoto = new ServiceHasPhoto();
                $serviceHasPhoto->photo_id = $photo->id;
                $model->link('serviceHasPhoto', $serviceHasPhoto);
            }

            if ($emails = NoticeEmail::getEmails()) {
                Email::send('notify-modified-service', $emails, [
                    'name' => $model->name,
                    'viewUrl' => Url::to([$model->url], true),
                    'updateUrl' => Url::to(['/admin/service/update', 'id' => $model->id], true),
                ]);
            }

            return $this->redirect('/profile/service');
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = Service::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $gallery = UploadedFile::getInstances($model, 'gallery');
            foreach ($gallery as $file) {
                $photo = Photo::createPhoto(
                    Service::MAX_GALLERY_IMAGE_SIZE,
                    Service::MAX_GALLERY_THUMB_WIDTH,
                    Service::MAX_GALLERY_THUMB_HEIGHT,
                    $file->tempName
                );
                $serviceHasPhoto = new ServiceHasPhoto();
                $serviceHasPhoto->photo_id = $photo->id;
                $model->link('serviceHasPhoto', $serviceHasPhoto);
            }
            $model->save();

            if ($emails = NoticeEmail::getEmails()) {
                Email::send('notify-modified-service', $emails, [
                    'name' => $model->name,
                    'viewUrl' => Url::to([$model->url], true),
                    'updateUrl' => Url::to(['/admin/service/update', 'id' => $model->id], true),
                ]);
            }

            return $this->redirect('/profile/service');
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = Service::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }
        $model->delete();

        return $this->redirect(['/profile/service']);
    }
}
