<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use app\models\User;
use app\models\Category;
use app\models\Module;
use app\models\NoticeEmail;
use yii\bootstrap\Nav;

class ModuleController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function ($rule, $action) {
                                if (!in_array(Yii::$app->user->identity->role, [User::ROLE_SUPERADMIN])) {
                                    throw new ForbiddenHttpException('Действие не разрешено.');
                                }
                                return true;
                            },
                        ],
                    ],
                ],
            ],
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['post']
                    ]
                ]
            ]
        );
    }
    
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Module::find()
        ]);
        
        if (Yii::$app->request->post('notice_email') !== null) {
            $emails_str = Yii::$app->request->post('notice_email');
            $emails = explode(',', $emails_str);
            $model_notice = NoticeEmail::find()->all();
            if ($model_notice) {
                NoticeEmail::deleteAll();
            }
            foreach ($emails as $email) {
                $model_notice = new NoticeEmail;
                $model_notice->email = trim($email);
                $model_notice->save();
            }
            
        }
        
        $emails = "";
        $model_notice = NoticeEmail::find()->all();
        if ($model_notice) {
            foreach ($model_notice as $res) {
                $emails .= $res->email . ", ";
            }
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'emails' => substr($emails, 0, -2),
        ]);
    }
    
    public function actionUpdateState()
    {
        $post = Yii::$app->request->post();
        $model = Module::findOne($post['id']);
        
        $model->state = $post['state'];
        
        if ($post['id'] == 2) { // если выбраны Закупки
            $editModel = Category::findOne(24); // найди модель Закупки, она под номером 24
            if ($post['state'] == 1) { // если установлена галочка                
                $editModel->visibility = 1; // сделай её видимой                
            } else { // если снята галочка                
                $editModel->visibility = 0; // сделай её невидимой               
            }
            $editModel->save(); // сохрани
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => $model->save(),
        ];
    }
}