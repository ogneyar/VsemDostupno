<?php
namespace app\commands;

use Yii;
use DateTime;
use Exception;
use yii\web\Response;
use yii\console\Controller;
// use app\models\User;
// use app\models\Account;
use app\models\SubscriberPayment;
// use app\models\SubscriberMessages;


class TestController extends Controller
{
    public function actionIndex()
    {
        $d = new DateTime();
        $date = $d->format('Y-m-d H:i:s');

        $subPay = new SubscriberPayment();
        // $subPay->id = 65;
        $subPay->user_id = 424;
        // $subPay->fullName = "";
        // сообщения для автоматики
        $subPay->created_at = $date;
        $subPay->number_of_times = 1;
        // $subPay->save();
        if (!$subPay->save()) {
            throw new Exception('Ошибка создания SubscriberPayment!');
        }

        return $date." тест.";
    }
}