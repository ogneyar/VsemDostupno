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


class SubscriberMonthPaymentController extends Controller
{
    public function actionIndex()
    {
        // формат вывода данных на экран
        // Yii::$app->response->format = Response::FORMAT_JSON;

        // Вывод данных на экран
        $response = ""; //\r\n\r\n";

        // отправлять ли сообщение на почту
        // $sendMessage = false;
        $sendMessage = true;

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
                        if ($user_deposit_total > 0) {                            
                            Account::swap($user_deposit, $admin_storage, $user_deposit_total, $message . " (ранее не оплаченого)", $sendMessage);
                            Account::swap($user_subscription, null, $user_deposit_total, $message . " (ранее не оплаченого)", false);
                            $amount = $amount + $user_deposit_total;
                        }
                    }
                }

                $d = new DateTime();
                $date = $d->format('Y-m-d H:i:s');

                // информация о том когда был последний платёж
                $subPay = SubscriberPayment::find()->where(['user_id' => $user->id])->one();
                if ($subPay) {
                    // последний платёж
                    $lastPay = $subPay->created_at;

                    $dateComps = date_parse($lastPay);
                    $year = $dateComps['year'];
                    $month = $dateComps['month'];

                    $newDateComps = date_parse($date);
                    $newYear = $newDateComps['year'];
                    $newMonth = $newDateComps['month'];

                    if ($newYear == $year) {
                        if ($newMonth == $month) {
                            continue;
                        }
                    }
                    
                }else {
                    $subPay = new SubscriberPayment();
                    $subPay->user_id = $user->id;
                }                

                // основной кошелёк пользователя (после уплаты долгов)
                $user_deposit = $user->getAccount(Account::TYPE_DEPOSIT);
                // обновлённые данные долгового кошелька
                $user_subscription = $user->getAccount(Account::TYPE_SUBSCRIPTION);

                if ($user_deposit->total >= $paySumm) { // если денег хватает
                    Account::swap($user_deposit, $admin_storage, $paySumm, $message, $sendMessage);
                }else { // иначе если не хватает
                    if ($user_deposit->total > 0) { // если хоть что-то на счету есть
                        $user_deposit_total = $user_deposit->total;
                        Account::swap($user_deposit, $admin_storage, $user_deposit_total, $message . " (частично)", $sendMessage);
                        Account::swap(null, $user_subscription, $paySumm - $user_deposit_total, $message . " (остаток суммы, которой не хватило для уплаты ЧВ)", $sendMessage);
                    }else { // иначе всю сумму записываем в долг
                        Account::swap(null, $user_subscription, $paySumm, $message . " (нет средств на основном счету)", $sendMessage);
                    }
                }
                $amount += $paySumm;

                $response = $response . "User_id=" . $user->id . " - членские взносы - " . $amount . "<br /><br />"; //\r\n\r\n";
                
                $subPay->created_at = $date;
                $subPay->amount = $amount;
                $subPay->save();
                
                // throw new Exception('Деление на ноль.');

            }catch(Exception $e) {
                $response = $response . $e->getMessage() . "<br /><br />"; //\r\n\r\n";
                // return $e->getMessage();
            }
            
        }

        if ($response) return "Вывод данных на экран:<br /><br />" . $response;
        
        return "Нет списаний.";

    }



}