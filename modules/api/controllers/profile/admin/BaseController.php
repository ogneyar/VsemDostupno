<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use app\models\User;

class BaseController extends Controller
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
                            if (Yii::$app->user->identity->entity->disabled ||
                            !in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
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
