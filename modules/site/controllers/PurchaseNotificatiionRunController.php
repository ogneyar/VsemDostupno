<?php

namespace app\modules\site\controllers;

use Yii;


class PurchaseNotificatiionRunController extends BaseController
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