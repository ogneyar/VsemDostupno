<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Member;

class UserController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'search' => ['get'],
                ],
            ],
        ]);
    }

    public function actionSearch($q = null, $id = null)
    {
        $out = [
            'results' => [
                [
                    'id' => '',
                    'text' => '',
                ],
            ],
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!is_null($q)) {
            $userQuery = User::find()
                ->orWhere('lastname like :q')
                ->orWhere('firstname like :q')
                ->orWhere('patronymic like :q')
                ->orWhere('number like :q')
                ->orWhere('phone like :q')
                ->orWhere('email like :q', [':q' => '%' . $q . '%'])
                ->andWhere('disabled = 0')
                // ->andWhere(['IN', 'role', User::getBuyerRoles()])
                ->orderBy([
                    'lastname' => SORT_ASC,
                    'firstname' => SORT_ASC,
                    'patronymic' => SORT_ASC,
                ]);

            $data = [];
            foreach ($userQuery->each() as $user) {
                $role = $user->roleName;
                if ($user->role == User::ROLE_PROVIDER) {
                    $member = Member::find()->where(['user_id' => $user->id])->one();
                    if ($member) {
                        $role = 'участник-поставщик';
                    } else {
                        continue;
                    }
                }
                
                $data[] = [
                    'id' => $user->id,
                    'text' => sprintf(
                        '%s %s %s (%s) - %.2f',
                        $user->lastname,
                        $user->firstname,
                        $user->patronymic,
                        $role,
                        $user->deposit->total
                    ),
                ];
            }

            if ($data) {
                $out['results'] = $data;
            }
        } elseif ($id > 0) {
            $user = User::find()
                ->andWhere('id = :id', [':id' => $id])
                ->andWhere('disabled = 0')
                // ->andWhere(['IN', 'role', User::getBuyerRoles()])
                ->one();
            if ($user) {
                $role = $user->roleName;
                if ($user->role == User::ROLE_PROVIDER) {
                    $member = Member::find()->where(['user_id' => $user->id])->one();
                    if ($member) {
                        $role = 'участник-поставщик';
                    } else {
                        return false;
                    }
                }
                $out['results'] = [
                    [
                        'id' => $user->id,
                        'text' => sprintf(
                            '%s %s %s (%s)',
                            $user->lastname,
                            $user->firstname,
                            $user->patronymic,
                            $role
                        ),
                    ],
                ];
            }
        }

        return $out;
    }
}
