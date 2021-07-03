<?php

namespace app\commands;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\console\Controller;
use app\models\Email;
use app\models\User;
use app\models\Category;
use app\models\Product;
use app\models\Order;
use app\models\OrderStatus;
use app\models\Parameter;
use app\models\Account;
use app\models\SubscriberPayment;

class WatcherController extends Controller
{
    const ROTATE_DAYS = 7;
    const TRIGGER_HOURS_OF_DAYS = 20;
    const SUPPORT_PERCENTS = 25;
    const MONTH_DAYS = 30;

    public function actionAll()
    {
        //$this->actionUpdateCategoryDates();
        //$this->actionCompliteOrders();
        //$this->actionDoSubscriberPayments();
        $this->actionSendExpiryNotifications();
        //$this->actionSendInventoryNotifications();
    }

    public function actionUpdateCategoryDates()
    {
        $purchaseCategory = Category::findOne(['slug' => Category::PURCHASE_SLUG]);
        if (!$purchaseCategory) return;

        $categoryQuery = $purchaseCategory->getAllChildrenQuery()
            ->andWhere('visibility != 0')
            ->orderBy(['name' => SORT_ASC]);

        ob_start();
        foreach ($categoryQuery->each() as $category) {
            if ($category->orderDate && $category->purchaseDate && 
            strtotime($category->orderDate) < strtotime($this->getCurrentDate())) {
                $productsQuery = $category->getAllProductsQuery()
                    ->andWhere('visibility != 0')
                    ->andWhere('published != 0')
                    ->orderBy(['name' => SORT_ASC]);
                
                if (!$productsQuery->all()) {
                    continue;
                }

                printf(
                    "%s (Заказы %s -> %s, Закупка %s -> %s):\n",
                    $category->name,
                    $category->orderDate, $this->getNextDate($category->orderDate),
                    $category->purchaseDate, $this->getNextDate($category->purchaseDate)
                );

                foreach ($productsQuery->each() as $product) {
                    printf("  * %s\n", $product->name);
                }

                $category->orderDate = $this->getNextDate($category->orderDate);
                $category->purchaseDate = $this->getNextDate($category->purchaseDate);
                $category->saveNode();
            }
        }
        $output = ob_get_clean();

        if ($output) {
            Email::send('update-category-dates', $this->getNotifyEmails(), [
                'date' => date('Y-m-d'),
                'list' => nl2br($output),
            ]);
        }
    }

    public function actionCompliteOrders()
    {
        $query = Order::find()
            ->joinWith('orderStatus')
            ->where('{{%order_status}}.type = :type', [':type' => OrderStatus::STATUS_VERIFIED]);

        $providers = [];
        foreach ($query->each() as $order) {
            if ($order->user && $order->user->disabled) {
                continue;
            }

            foreach ($order->purchaseOrderHasProducts as $orderHasProduct) {
                if (strtotime($orderHasProduct->orderDate) < strtotime($this->getCurrentDate())) {
                    $providers = $this->putOrderHasProductToProvider($providers, $orderHasProduct);
                    $orderHasProduct->purchase = 1;
                    $orderHasProduct->save();
                }
            }
            if ($order->canCompleted()) {
                $order->order_status_id = OrderStatus::getIdByType(OrderStatus::STATUS_COMPLETED);
                $order->save();

                $defaultPartner = User::findOne(['email' => Yii::$app->params['defaultPartnerEmail']]);

                $message = sprintf('Премирование за привлечение %s', $order->shortName);
                if ($order->user && $order->user->recommender) {
                    Account::swap(null, $order->user->recommender->bonus, $order->getProductPriceTotal('invite_price'), $message);
                } else {
                    Account::swap(null, $defaultPartner->group, $order->getProductPriceTotal('invite_price'), $message);
                }

                $message = sprintf('Премия за Партнёрские группы с покупки %s', $order->shortName);
                if ($order->user && $order->user->member) {
                    Account::swap(null, $order->user->member->partner->group, $order->getProductPriceTotal('group_price'), $message);
                } else {
                    Account::swap(null, $defaultPartner->group, $order->getProductPriceTotal('group_price'), $message);
                }

                $message = sprintf('Складской сбор с покупки %s', $order->shortName);
                Account::swap(null, $defaultPartner->storage, $order->getProductPriceTotal('storage_price'), $message);

                $message = sprintf('Отчисления в фонд Содружества с покупки %s', $order->shortName);
                Account::swap(null, $defaultPartner->fraternity, $order->getProductPriceTotal('fraternity_price'), $message);
            }
        }

        foreach ($providers as $email => $provider) {
            $productQuery = Product::find()
                ->where(['IN', 'id', array_keys($provider)])
                ->orderBy(['name' => SORT_ASC]);

            ob_start();
            foreach ($productQuery->each() as $product) {
                $quantity = $provider[$product->id];
                printf("%.2f - %s\n", $quantity, $product->name);
            }
            $output = ob_get_clean();

            Email::send(
                'notify-purchase',
                $this->getNotifyEmails($email != User::ROLE_ADMIN ? $email : Yii::$app->params['adminEmail']), [
                'date' => date('Y-m-d'),
                'list' => nl2br($output),
            ]);
        }
    }

    public function actionDoSubscriberPayments()
    {
        $subscriberPaymentAmount = (int) Parameter::getValueByName('subscriber-payment');
        if (!$subscriberPaymentAmount) return;

        $developer = User::findOne(['email' => Yii::$app->params['devEmail']]);

        $query = User::find()
            ->where('disabled = 0')
            ->andWhere('id != :id', [':id' => $developer->id])
            ->andWhere(['IN', 'role', [User::ROLE_MEMBER, User::ROLE_PARTNER, User::ROLE_PROVIDER]]);

        $total = 0;
        foreach ($query->each() as $user) {
            if ($user->role == User::ROLE_PROVIDER) {
                $member = User::findOne(['role' => User::ROLE_MEMBER, 'passport' => $user->passport]);
                if ($member) {
                    continue;
                }
            }

            $subscriberPayment = SubscriberPayment::find()
                ->where('user_id = :user_id', [':user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(1)
                ->one();

            $lastDate = $subscriberPayment ?
                new \DateTime($subscriberPayment->created_at) :
                new \DateTime($user->created_at);
            $interval = $lastDate->diff(new \DateTime('now'));
            if ($interval->days >= User::SUBSCRIBER_MONTHS_INTERVAL * self::MONTH_DAYS) {
                if (Account::swap(null, $user->subscription, -$subscriberPaymentAmount, 'Членский взнос')) {
                    $total += $subscriberPaymentAmount;
                    $subscriberPayment = new SubscriberPayment([
                        'user_id' => $user->id,
                        'amount' => $subscriberPaymentAmount,
                    ]);
                    $subscriberPayment->save();
                }
            }
        }

        if ($total) {
            $amount = round(($total / 100.) * self::SUPPORT_PERCENTS, 2);
            Account::swap(null, $developer->deposit, $amount, 'Оплата за поддержку');
        }
    }

    public function actionSendExpiryNotifications()
    {
        $products = Product::find()
            ->where('visibility != 0 AND expiry_timestamp < NOW() + INTERVAL 2 DAY AND expiry_timestamp > NOW()')
            ->orderBy(['name' => SORT_ASC])
            ->all();

        ob_start();
        foreach ($products as $product) {
            echo Html::tag(
                'li',
                Html::a(
                    $product->name,
                    Url::to(['/admin/product/update', 'id' => $product->id], true),
                    [
                        'target' => '_blank',
                    ]
                ) . ' - ' .
                mb_substr($product->expiry_timestamp, 0, 10, Yii::$app->charset)
            );
        }
        $output = ob_get_clean();

        if ($output) {
            Email::send(
                'notify-product-expiry',
                $this->getNotifyEmails(Yii::$app->params['defaultPartnerEmail']), [
                'list' => Html::tag('ul', $output),
            ]);
        }
    }

    public function actionSendInventoryNotifications()
    {
        $products = Product::find()
            ->where('visibility != 0 AND inventory <= min_inventory')
            ->orderBy(['name' => SORT_ASC])
            ->all();

        ob_start();
        foreach ($products as $product) {
            echo Html::tag(
                'li',
                Html::a(
                    $product->name,
                    Url::to(['/admin/product/update', 'id' => $product->id], true),
                    [
                        'target' => '_blank',
                    ]
                ) .
                sprintf(' - %d (минимум: %d)', $product->inventory, $product->min_inventory)
            );
        }
        $output = ob_get_clean();

        if ($output) {
            Email::send(
                'notify-product-min-inventory',
                $this->getNotifyEmails(Yii::$app->params['defaultPartnerEmail']), [
                'list' => Html::tag('ul', $output),
            ]);
        }
    }

    private function getNextDate($date)
    {
        $lastDate = new \DateTime($date);
        $interval = $lastDate->diff(new \DateTime('now'));

        return  date(
            'Y-m-d',
            $lastDate->getTimestamp() +
            strtotime('1 day', 0) * ($interval->days - $interval->days % self::ROTATE_DAYS + self::ROTATE_DAYS)
        );
    }

    private function getCurrentDate()
    {
        $str = sprintf('%d hour%s', 24 - self::TRIGGER_HOURS_OF_DAYS, 24 - self::TRIGGER_HOURS_OF_DAYS > 1 ? 's' : '');

        return date('Y-m-d', strtotime($str));
    }

    private function getNotifyEmails($to = [])
    {
        $to = !is_array($to) ? [$to] : $to;

        return array_unique(
            array_merge($to, [
                Yii::$app->params['adminEmail'],
            ])
        );
    }

    private function putOrderHasProductToProvider($providers, $orderHasProduct)
    {
        $email = $orderHasProduct->product->provider ? $orderHasProduct->product->provider->user->email : User::ROLE_ADMIN;

        if (!isset($providers[$email])) {
            $providers[$email] = [];
        }

        $productId = $orderHasProduct->product->id;
        if (!isset($providers[$email][$productId])) {
            $providers[$email][$productId] = $orderHasProduct->quantity;
        } else {
            $providers[$email][$productId] += $orderHasProduct->quantity;
        }

        return $providers;
    }
}
