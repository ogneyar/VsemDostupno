<?php

namespace app\modules\admin\controllers;

use Yii;
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
        
        $user = User::find()->where(['disabled' => 0, 'role' => [User::ROLE_SUPERADMIN]])->one();
        $account = Account::find()->where(['user_id' => $user->id,'type' => 'subscription'])->one();
        
        $subscriber_messages = SubscriberMessages::find()->all();

        return $this->render('index', [
            'account' => floor($account->total),
            'superadmin' => $superadmin,
            'web' => $web,
            'request' => Yii::$app->request,
            'subscriber_messages' => $subscriber_messages,
        ]);
    }
}
