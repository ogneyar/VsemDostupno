<?php

namespace app\modules\purchase\controllers\site;

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
                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_MEMBER, User::ROLE_PROVIDER, User::ROLE_PARTNER])) {
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
                    'js/purchase/site.js?v=' . (int) (time() / 3600),
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