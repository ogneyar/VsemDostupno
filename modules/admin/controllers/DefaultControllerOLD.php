<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\modules\admin\models\LoginForm;

class DefaultController extends BaseController
{
    public function actionIndex()
    {
        return $this->redirect('/admin/product');
    }
}
