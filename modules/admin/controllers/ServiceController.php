<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\db\Query;
use app\models\Category;
use app\models\Service;
use app\models\Photo;
use app\models\ServiceHasPhoto;

/**
 * ServiceController implements the CRUD actions for Service model.
 */
class ServiceController extends BaseController
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

    /**
     * Lists all Service models.
     * @return mixed
     */
    public function actionIndex()
    {
        $get = Yii::$app->request->get();
        $category_id = isset($get['category_id']) ? $get['category_id'] : 'all';
        $categories = ArrayHelper::merge(
            [
                'all' => '&ndash; Все услуги &ndash;',
                'none' => '&ndash; Услуги без категорий &ndash;',
            ],
            Category::getSelectTree(Category::findOne(74))
        );//Category::findOne(361) // 

        if (is_numeric($category_id)) {
            $category = Category::findOne(['id' => $category_id]);
            if ($category) {
                $query = $category->getAllServicesQuery();
            } else {
                $query = Service::find()->where('FALSE');
            }
        } elseif ($category_id == 'none') {
            $query = Service::find();
            $query->joinWith('categories')
                ->where('{{%category}}.id IS NULL');
        } else {
            $query = Service::find();
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        return $this->render('index', [
            'category_id' => $category_id,
            'categories' => $categories,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Service model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Service model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Service(['visibility' => 1, 'published' => 1]);

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
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Service model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

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
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Service model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Service model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Service the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Service::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
