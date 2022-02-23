<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\SubscriberMessages;

use app\models\User;
use app\models\Account;
use yii\web\Response;

/**
 * SubscriberPaymentController implements the CRUD actions for Page model.
 */
class SubscriberPaymentController extends BaseController
{
    /**
     * Lists all Page models.
     * @return mixed
     */
    public function actionIndex()
    {
        // if (Yii::$app->request->get()) { 
        //     Yii::$app->response->format = Response::FORMAT_JSON;
        //     return  [ 'ok' => true, 'message' => 'Товар найден.', ];
        // }

        $constants = require(__DIR__ . '/../../../config/constants.php');
        $web = $constants["WEB"];

        $superadmin = false;
        if (Yii::$app->user->identity->role == User::ROLE_SUPERADMIN) $superadmin = true;
        // $users = User::find()->where(['role' => [User::ROLE_MEMBER,User::ROLE_PARTNER,User::ROLE_PROVIDER]])->all();
        $users = User::find()->where(['disabled' => 0, 'role' => [User::ROLE_SUPERADMIN]])->all();
        $accounts = [];
        foreach($users as $user)
        {
            // $accounts[] = Account::find()->where(['user_id' => $user->id,'type' => ['deposit', 'subscription']])->all();
            $accounts[] = Account::find()->where(['user_id' => $user->id,'type' => 'subscription'])->all();
            // $accounts[] = Account::find()->where(['user_id' => $user->id])->all();
        }

        
        // $dataProvider = new ActiveDataProvider([
        //     'query' => SubscriberMessages::find(),
        //     'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        //     ]);
            
        $subscriber_messages = SubscriberMessages::find()->all();

        return $this->render('index', [
            // 'dataProvider' => $dataProvider,
            'account' => floor($accounts[0][0]->total),
            'superadmin' => $superadmin,
            'web' => $web,
            'request' => Yii::$app->request,
            'subscriber_messages' => $subscriber_messages,
        ]);
    }
}
