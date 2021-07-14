<?php

namespace app\modules\mailing\controllers\site;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\User;

class BaseController extends Controller
{
    public $layout = '@app/modules/site/views/layouts/site';
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
                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_MEMBER, User::ROLE_PARTNER, User::ROLE_PROVIDER])) {
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
                    'css/site/style.css?v=' . (int) (time() / 3600),
                ],
                'js' => [
                    'js/mailing/common.js?v=' . (int) (time() / 3600),
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