<?php

namespace app\modules\api\controllers\profile\partner;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;

class MemberController extends BaseController
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
                ->joinWith('member')
                ->orWhere('{{%user}}.lastname like :q')
                ->orWhere('{{%user}}.firstname like :q')
                ->orWhere('{{%user}}.patronymic like :q')
                ->orWhere('{{%user}}.number like :q')
                ->orWhere('{{%user}}.phone like :q')
                ->orWhere('{{%user}}.email like :q', [':q' => '%' . $q . '%'])
                ->andWhere('{{%member}}.partner_id = :partner_id', [':partner_id' => Yii::$app->user->identity->entity->partner->id])
                ->andWhere('{{%user}}.disabled = 0')
                ->andWhere(['IN', '{{%user}}.role', User::getBuyerRoles()])
                ->orderBy([
                    '{{%user}}.lastname' => SORT_ASC,
                    '{{%user}}.firstname' => SORT_ASC,
                    '{{%user}}.patronymic' => SORT_ASC,
                ]);

            $data = [];
            foreach ($userQuery->each() as $user) {
                $data[] = [
                    'id' => $user->id,
                    'text' => sprintf(
                        '%s %s %s (%s) - %.2f',
                        $user->lastname,
                        $user->firstname,
                        $user->patronymic,
                        $user->roleName,
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
                ->andWhere(['IN', 'role', User::getBuyerRoles()])
                ->one();
            if ($user) {
                $out['results'] = [
                    [
                        'id' => $user->id,
                        'text' => sprintf(
                            '%s %s %s (%s)',
                            $user->lastname,
                            $user->firstname,
                            $user->patronymic,
                            $user->roleName
                        ),
                    ],
                ];
            }
        }

        return $out;
    }
}
