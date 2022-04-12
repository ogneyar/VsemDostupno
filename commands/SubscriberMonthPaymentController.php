<?php
namespace app\commands;

use Yii;
use DateTime;
use Exception;
use yii\web\Response;
use yii\console\Controller;
use app\models\User;
use app\models\Account;
use app\models\SubscriberPayment;
use app\models\SubscriberMessages;


class SubscriberMonthPaymentController extends Controller
{
    public function actionIndex()
    {
        // формат вывода данных на экран
        // Yii::$app->response->format = Response::FORMAT_JSON;

        // Вывод данных на экран
        $response = ""; //\r\n\r\n";

        $constants = require(__DIR__ . '/../config/constants.php');
        $web = $constants["WEB"];

        // отправлять ли сообщение на почту
        if ($web == "") $sendMessage = false;
        else $sendMessage = true;

        // сообщение которое записывается в AccountLog
        $message = "Списание членского взноса";

        // супер админ
        $admin = User::find()->where(['role' => User::ROLE_SUPERADMIN, 'disabled' => false])->one();
        
        // счёт у супер админа, где хранится сумма членских взносов
        $admin_subscription = Account::find()->where(['user_id' => $admin->id,'type' => 'subscription'])->one();
        // сумма членского взноса
        $paySumm = $admin_subscription->total;

        if ($paySumm == 0) return "Функция отключена администратором!";

        // кошелёк, где хранятся членские взносы
        $admin_storage = $admin->getAccount(Account::TYPE_STORAGE);

        // ищем всех контрагентов
        $users = User::find()
            ->where(['role' => User::ROLE_MEMBER, 'disabled' => false])
            ->orWhere(['role' => User::ROLE_PARTNER, 'disabled' => false])
            ->orWhere(['role' => User::ROLE_PROVIDER, 'disabled' => false])
            ->all();

        foreach ($users as $user) {
            try {
                // подсчёт общей суммы, снятой в этом месяце
                $amount = 0;

                // заглушка не время теста (тест над счётом Алексея - user_id = 367)
                // if ($user->id != 367) continue;
                // if ($user->id != 345) continue; // или над моим

                // долги по членским взносам копятся здесь (в положительном значении)
                $user_subscription = $user->getAccount(Account::TYPE_SUBSCRIPTION);
                // итоговая сумма долга
                $user_subscription_total = $user_subscription->total;

                $flag = false; // есть ли долг?

                if ($user_subscription_total > 0) { // значит есть не оплаченный взнос
                    // основной кошелёк пользователя
                    $user_deposit = $user->getAccount(Account::TYPE_DEPOSIT);
                    // итоговая сумма кошелька
                    $user_deposit_total = $user_deposit->total;
                    if ($user_deposit_total > $user_subscription_total) { // если достаточно средств на счету
                        Account::swap($user_deposit, $admin_storage, $user_subscription_total, $message . " (ранее не оплаченого)", $sendMessage);
                        Account::swap($user_subscription, null, $user_subscription_total, $message . " (ранее не оплаченого)", false);
                        $amount = $amount + $user_subscription_total;
                    }else { // если недостаточно...
                        // if ($user_deposit_total > 0) {                            
                        //     Account::swap($user_deposit, $admin_storage, $user_deposit_total, $message . " (ранее не оплаченого)", $sendMessage);
                        //     Account::swap($user_subscription, null, $user_deposit_total, $message . " (ранее не оплаченого)", false);
                        //     $amount = $amount + $user_deposit_total;
                        // }
                        $flag = true; // есть долги
                    }
                }
                
                // основной кошелёк пользователя (после уплаты долгов)
                $user_deposit = $user->getAccount(Account::TYPE_DEPOSIT);
                // обновлённые данные долгового кошелька
                $user_subscription = $user->getAccount(Account::TYPE_SUBSCRIPTION);


                $d = new DateTime();
                $date = $d->format('Y-m-d H:i:s');
                $newDateComps = date_parse($date);
                $newYear = $newDateComps['year'];
                $newMonth = $newDateComps['month'];

                
                $dateComps = date_parse($user->created_at);                
                $year = $dateComps['year'];
                $month = $dateComps['month'];
                // проверка, если пользователь зарегестрирован в этом месяце, то пропускаем
                if ($newYear == $year && $newMonth == $month) continue;
                
                // сообщения для админа на стр Членские Взносы
                $subMess = new SubscriberMessages(); 
                $subMess->user_id = $user->id;
                $subMess->created_at = $date;

                // информация о том когда был последний платёж
                $subPay = SubscriberPayment::find()->where(['user_id' => $user->id])->one();
                if ($subPay) {
                    // последний платёж
                    $lastPay = $subPay->created_at;

                    $dateComps = date_parse($lastPay);
                    $year = $dateComps['year'];
                    $month = $dateComps['month'];

                    // $newDateComps = date_parse($date);
                    // $newYear = $newDateComps['year'];
                    // $newMonth = $newDateComps['month'];

                    if ($newYear == $year) {
                        if ($newMonth == $month) { // если в этом месяце уже был платёж
                            // если нет долга, а в базе записано что он есть, то обнуляем
                            if ( ! $flag && $subPay->number_of_times > 0) {
                                $subPay->number_of_times = 0;
                                $subPay->save();
                            }
                            continue; // пропускаем если в этом месяце уже был платёж
                        }
                    }

                    if ($subPay->number_of_times >= 3 && $flag) { // если долг более 3х месяцев
                        // сообщения для автоматики
                        $subPay->created_at = $date;
                        $subPay->save();
                        
                        // сообщения для админа на стр Членские Взносы
                        $subMess->amount = $user_subscription->total * (-1);
                        $subMess->save();
                        
                        continue; // пропускаем если долг более 3х месяцев
                    }
                    
                }else {
                    $subPay = new SubscriberPayment();
                    $subPay->user_id = $user->id;
                }                


                $number_of_times = 0; // количество неоплаченных раз (месяцев)

                $newFlag = false; // есть ли долг (ещё один флаг нужен потому что ниже используется первый флаг)
                if ($user_deposit->total >= $paySumm && ! $flag) { // если денег хватает и нет не погашенных долгов
                    Account::swap($user_deposit, $admin_storage, $paySumm, $message, $sendMessage);
                }else { // иначе если не хватает
                    // if ($user_deposit->total > 0) { // если хоть что-то на счету есть
                    //     $user_deposit_total = $user_deposit->total;
                    //     Account::swap($user_deposit, $admin_storage, $user_deposit_total, $message . " (частично)", $sendMessage);
                    //     Account::swap(null, $user_subscription, $paySumm - $user_deposit_total, $message . " (остаток суммы, которой не хватило для уплаты ЧВ)", $sendMessage);
                    // }else { 
                        // иначе всю сумму записываем в долг
                        Account::swap(null, $user_subscription, $paySumm, $message . " (не хватает средств на основном счету)", $sendMessage);
                        if ($subPay->number_of_times > 0) $number_of_times = $subPay->number_of_times + 1;
                        else $number_of_times = 1;
                        $newFlag = true;
                    // }
                }
                // сообщения для автоматики
                $subPay->created_at = $date;
                $subPay->number_of_times = $number_of_times;
                $subPay->save();

                // сообщения для админа на стр Членские Взносы
                if ($amount) { // если произвелась оплата долгов
                    if ( ! $newFlag) { // если не появилось долгов
                        $subMess->amount = $amount + $paySumm;
                    }else {
                        $subMess->amount = $amount;

                        // ещё одно сообщение для админа на стр Членские Взносы (о долге)
                        $subMess2 = new SubscriberMessages(); 
                        $subMess2->user_id = $user->id;
                        $subMess2->created_at = $date;
                        $subMess2->amount = $paySumm * (-1);                    
                        $subMess2->save();
                    }
                }else {
                    if ( ! $newFlag) $subMess->amount = $paySumm;
                    else $subMess->amount = $paySumm * (-1);                    
                }

                $subMess->save();

                $response = $response . "User_id=" . $user->id . " - членские взносы - " . $paySumm . "<br /><br />"; //\r\n\r\n";
                                
                // throw new Exception('Деление на ноль.');

            }catch(Exception $e) {
                $response = $response . "(Exception)" . $e->getMessage() . "<br /><br />"; //\r\n\r\n";
                // return $e->getMessage();
            }
            
        }

        if ($response) return "Вывод данных на экран:<br /><br />" . $response;
        
        return "Нет списаний.";

    }



}