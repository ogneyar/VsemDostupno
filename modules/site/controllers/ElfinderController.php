<?php

namespace app\modules\site\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use mihaildev\elfinder\PathController;

class ElfinderController extends PathController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $identity = Yii::$app->user->identity;

                            if (!in_array($identity->role, $this->access)) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ];
    }
}
