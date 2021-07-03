<?php

namespace app\modules\site\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Page;
use app\models\Category;

class PageController extends BaseController
{
    public function actionSlug($slug)
    {
        $model = Page::find()
            ->where('slug = :slug AND visibility != 0', [':slug' => $slug])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }
        
        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();

        return $this->render('index', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }
}
