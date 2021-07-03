<?php

namespace app\modules\site\controllers;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public $layout = 'site';
    protected $identity;
    protected $defaultRoute = '/';

    public function actions()
    {
        Yii::$app->assetManager->bundles = [
            'app\assets\AppAsset' => [
                'css'=>[
                    'css/site/style.css?v=' . (int) (time() / 3600),
                ],
                'js'=>[
                    'js/site/WidgetHelpers.js?v=' . (int) (time() / 3600),
                    'js/site/CartHelpers.js?v=' . (int) (time() / 3600),
                    'js/site/site.js?v=' . (int) (time() / 3600),
                ],
            ],
        ];

        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'error') {
            $this->layout = 'main';
        }

        if (!Yii::$app->user->isGuest) {
            $this->identity = Yii::$app->user->identity;
        }

        return parent::beforeAction($action);
    }
}
