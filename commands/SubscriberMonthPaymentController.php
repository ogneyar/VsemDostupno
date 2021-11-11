<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\User;
use app\models\Account;

class SubscriberMonthPaymentController extends Controller
{
    public function actionIndex()
    {
        $user = User::find()->where(['role' => User::ROLE_MEMBER, 'role' => User::ROLE_PARTNER, 'role' => User::ROLE_PROVIDER])->all();
        // $provider_account = Account::findOne(['user_id' => $provider_balance->provider->user_id]);
        
        // var_dump($user);
        
        return $this->render('index', ['user' => $user]);
    }
}