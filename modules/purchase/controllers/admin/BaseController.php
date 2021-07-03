<?php

namespace app\modules\purchase\controllers\admin;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;

class BaseController extends Controller
{
    public $layout = '@app/modules/admin/views/layouts/main';
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
                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ];
    }
    
    public function actions()
    {
        Yii::$app->assetManager->bundles = [
            'app\assets\AppAsset' => [
                'css'=>[
                    'css/admin/style.css?v=' . (int) (time() / 3600),
                ],
                'js' => [
                    'js/purchase/admin2.js?v=' . (int) (time() / 3600),
                ]
            ],
        ];
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}