<?php

namespace app\modules\admin\controllers;

use Yii;


class TestController extends BaseController
{
   
    /**
     * Tests.
     * @return mixed
     */
    public function actionIndex()
    {       
        return $this->render('index', []);
    }

}