<?php

namespace app\modules\site\controllers\profile\provider;

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
use app\models\Product;
use app\models\ProviderHasProduct;
use app\models\Photo;
use app\models\ProductHasPhoto;
use app\models\NoticeEmail;

class ProductController extends BaseController
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

                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_PROVIDER])) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
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
        $query = Product::find()
            ->leftJoin('{{%provider_has_product}}', '{{%provider_has_product}}.product_id = {{%product}}.id')
            ->where('{{%provider_has_product}}.provider_id = :provider_id', [':provider_id' => $this->identity->entity->provider->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Product([
            'visibility' => 1,
            'published' => 1,
            'price' => 0,
            'member_price' => 0,
            'partner_price' => 0,
            'storage_price' => 0,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save();

            $providerHasProduct = new ProviderHasProduct();
            $providerHasProduct->product_id = $model->id;
            $this->identity->entity->provider->link('providerHasProduct', $providerHasProduct);

            $gallery = UploadedFile::getInstances($model, 'gallery');
            foreach ($gallery as $file) {
                $photo = Photo::createPhoto(
                    Product::MAX_GALLERY_IMAGE_SIZE,
                    Product::MAX_GALLERY_THUMB_WIDTH,
                    Product::MAX_GALLERY_THUMB_HEIGHT,
                    $file->tempName
                );
                $productHasPhoto = new ProductHasPhoto();
                $productHasPhoto->photo_id = $photo->id;
                $model->link('productHasPhoto', $productHasPhoto);
            }

            if ($emails = NoticeEmail::getEmails()) {
                Email::send('notify-modified-product', $emails, [
                    'name' => $model->name,
                    'viewUrl' => Url::to([$model->url], true),
                    'updateUrl' => Url::to(['/admin/product/update', 'id' => $model->id], true),
                ]);
            }

            return $this->redirect('index');
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = Product::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $gallery = UploadedFile::getInstances($model, 'gallery');
            foreach ($gallery as $file) {
                $photo = Photo::createPhoto(
                    Product::MAX_GALLERY_IMAGE_SIZE,
                    Product::MAX_GALLERY_THUMB_WIDTH,
                    Product::MAX_GALLERY_THUMB_HEIGHT,
                    $file->tempName
                );
                $productHasPhoto = new ProductHasPhoto();
                $productHasPhoto->photo_id = $photo->id;
                $model->link('productHasPhoto', $productHasPhoto);
            }
            $model->save();

            if ($emails = NoticeEmail::getEmails()) {
                Email::send('notify-modified-product', $emails, [
                    'name' => $model->name,
                    'viewUrl' => Url::to([$model->url], true),
                    'updateUrl' => Url::to(['/admin/product/update', 'id' => $model->id], true),
                ]);
            }

            return $this->redirect('index');
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = Product::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }
        $model->delete();

        return $this->redirect('index');
    }
}
