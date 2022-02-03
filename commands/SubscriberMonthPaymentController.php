<?php
namespace app\commands;

use Yii;
use yii\web\Response;
use yii\console\Controller;
use app\models\User;
use app\models\Account;


class SubscriberMonthPaymentController extends Controller
{
    public function actionIndex()
    {
        // $user = User::find()->where(['role' => User::ROLE_MEMBER, 'role' => User::ROLE_PARTNER, 'role' => User::ROLE_PROVIDER])->all();
        // $provider_account = Account::findOne(['user_id' => $provider_balance->provider->user_id]);
        
        // отправлять ли сообщение на почту
        $sendMessage = false;

        // сообщение которое записывается в AccountLog
        $message = "Списание членского взноса";

        $admin = User::find()->where(['role' => User::ROLE_SUPERADMIN, 'disabled' => false])->one();
        
        $account = Account::find()->where(['user_id' => $admin->id,'type' => 'subscription'])->one();
        // сумма членского взноса
        $paySumm = $account->total;

        $users = User::find()->where(['role' => User::ROLE_MEMBER, 'role' => User::ROLE_PARTNER, 'role' => User::ROLE_PROVIDER, 'disabled' => false])->all();

        $yes = false;
        foreach ($users as $user) {
            // Account::swap($user->getAccount(Account::TYPE_DEPOSIT), $admin->getAccount(Account::TYPE_STORAGE), -1, $message, $sendMessage);
            // Account::swap(Account::find()->where(['user_id' => 367,'type' => 'deposite'])->one(), $admin->getAccount(Account::TYPE_STORAGE), 1, $message, $sendMessage);

            // пополнение счёта Алексея
            // Account::swap(null, User::findOne(367)->getAccount(Account::TYPE_DEPOSIT), 10, $message, $sendMessage);
            Account::swap(User::findOne(367)->getAccount(Account::TYPE_DEPOSIT), $admin->getAccount(Account::TYPE_STORAGE), 1, $message, $sendMessage);

            $yes = true;

            return $admin->getAccount(Account::TYPE_STORAGE)->total;
            // return $user->getAccount(Account::TYPE_DEPOSIT)->total;
            // return User::findOne(367)->role;
            // return User::findOne(367)->getAccount(Account::TYPE_DEPOSIT)->total;

            break;
            
        }


        // var_dump($user);

        Yii::$app->response->format = Response::FORMAT_JSON;

        // return true;
        // return $account->total;
        return $yes;
        
    }
}