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
        $config = require(__DIR__ . '/../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];
        
        return $this->redirect($baseUrl . 'admin/product');
    }
}
