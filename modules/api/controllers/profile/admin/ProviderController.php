<?php

namespace app\modules\api\controllers\profile\admin;

use Yii;
use yii\web\Response;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\Provider;

class ProviderController extends BaseController
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
            $providerQuery = Provider::findBySql('SELECT * FROM provider INNER JOIN user on provider.user_id=user.id WHERE provider.name LIKE :q OR user.lastname LIKE :q',[':q'=>'%'.$q.'%'])
               //->orWhere('name like :q', [':q' => '%'.$q.'%'])
                ->orderBy([
                    'name' => SORT_ASC,
                ]);

            $data = [];
            foreach ($providerQuery->each() as $provider) {
                $data[] = [
                    'id' => $provider->id,
                    'text' => sprintf(
                        '%s',
                        $provider->name
                    ).' / '.$provider->user->lastname.' '.$provider->user->firstname .' '.$provider->user->patronymic
                ];
            }

            if ($data) {
                $out['results'] = $data;
            }
        } elseif ($id > 0) {
            $provider = Provider::find()
                ->andWhere('id = :id', [':id' => $id])
                ->one();
            if ($provider) {
                $out['results'] = [
                    [
                        'id' => $provider->id,
                        'text' => sprintf(
                            '%s',
                            $provider->name
                        ),
                    ],
                ];
            }
        }

        return $out;
    }

    public function actionIdSearch($q = null, $id = null)
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
            $providerQuery = Provider::findBySql('SELECT * FROM provider WHERE provider.name LIKE :q',[':q'=>'%'.$q.'%'])
                //->orWhere('name like :q', [':q' => '%'.$q.'%'])
                ->orderBy([
                    'name' => SORT_ASC,
                ]);

            $data = [];
            foreach ($providerQuery->each() as $provider) {
                $data[] = [
                    'id' => $provider->id,
                    'text' => sprintf(
                            '%s',
                            $provider->name
                        ).' / '.$provider->user->lastname.' '.$provider->user->firstname .' '.$provider->user->patronymic
                ];
            }

            if ($data) {
                $out['results'] = $data;
            }
        } elseif ($id > 0) {
            $provider = Provider::find()
                ->andWhere('id = :id', [':id' => $id])
                ->one();
            if ($provider) {
                $out['results'] = [
                    [
                        'id' => $provider->id,
                        'text' => sprintf(
                            '%s',
                            $provider->name
                        ),
                    ],
                ];
            }
        }

        return $out;
    }
}