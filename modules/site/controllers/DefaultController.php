<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Html;
use app\models\Category;

class DefaultController extends BaseController
{
    const MAX_MAIN_PAGE_ITEMS = 8;

    public function actionIndex()
    {
        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
        
        foreach ($menu_first_level as $menu_f_l) {
            if ($menu_f_l->name == "Товары") $newArray[] = $menu_f_l;    
        }
        foreach ($menu_first_level as $menu_f_l) {
            if ($menu_f_l->name != "Товары") $newArray[] = $menu_f_l;    
        }
        $menu_first_level = $newArray;
        
        return $this->render('index', [
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }

    protected function getCategoryProducts($slug)
    {
        $category = Category::findOne(['slug' => $slug]);

        if ($category) {
            return $category->getAllProductsQuery()
                ->andWhere('visibility != 0')
                ->andWhere('published != 0')
                ->orderBy('RAND()')
                ->limit(self::MAX_MAIN_PAGE_ITEMS)
                ->all();
        }

        return [];
    }

    protected function getServices()
    {
        $category = Category::findOne(['slug' => Category::SERVICE_SLUG]);

        if ($category) {
            return $category->getAllServicesQuery()
                ->andWhere('visibility != 0')
                ->andWhere('published != 0')
                ->orderBy('RAND()')
                ->limit(self::MAX_MAIN_PAGE_ITEMS)
                ->all();
        }

        return [];
    }
}
