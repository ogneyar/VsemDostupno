<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Category;
use app\models\User;

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
        
        // $tg_view = false;
        // if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
        //     $tg_view = true;
        // }

        return $this->render('index', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
            // 'tg_view' => $tg_view,
        ]);
    }
}
