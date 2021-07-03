<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Category;

class CategoryController extends BaseController
{
    public function actionIndex($id)
    {
        $model = Category::find()
            ->where('visibility != 0')
            ->andWhere('id = :id OR slug = :slug', [':id' => $id, ':slug' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        if ($model->slug && $model->slug != $id) {
            return $this->redirect($model->url);
        }
        
        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();

        return $this->render('index', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }
}
