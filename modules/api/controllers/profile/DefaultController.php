<?php

namespace app\modules\api\controllers\profile;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\Exception;
use app\models\User;
use app\modules\api\models\profile\UserSearching;

class DefaultController extends Controller
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
                            return true;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionSearchUser()
    {
        try {
            $output = '';
            $message = '';
            $user_id = '';
            $userSearching = new UserSearching();

            if (!$userSearching->load(Yii::$app->request->post()) || !$userSearching->validate()) {
                $error = $userSearching->getFirstError('search');
                throw new Exception($error ? $error : 'Неизвестная ошибка.');
            }

            $user = User::find()
                ->andWhere('id != :id', [':id' => Yii::$app->user->identity->entity->id])
                ->andWhere('email = :email OR  number = :number', [
                    ':email' => $userSearching->search,
                    ':number' => $userSearching->search,
                ])
                ->andWhere(['NOT IN', 'id', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN]])
                ->one();

            if (!$user) {
                throw new Exception('Пользователь не найден.');
            }

            $output = $user->fullName;
            $user_id = $user->id;

        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'output' => $output,
            'message' => $message,
            'user_id' => $user_id,
        ];
    }
}
