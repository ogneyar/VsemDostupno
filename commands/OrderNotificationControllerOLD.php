<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Order;
use app\models\Partner;
use app\models\Provider;
use app\models\ProviderNotification;
use app\models\User;
use app\models\NoticeEmail;

class OrderNotificationController extends Controller
{
    public function actionIndex()
    {
        $date = date('Y-m-d');
        
        $providers = Order::getProviderIdByDate($date, 1);
        if ($providers) {
            foreach ($providers as $provider) {
                if ($provider['provider_id'] != 0) {
                    $partners = Order::getPartnerIdByProvider($date, $provider['provider_id'], 1);
                    if ($partners) {
                        foreach ($partners as $partner) {
                            $details = Order::getOrderDetailsByProviderPartner($date, $provider['provider_id'], $partner['partner_id'], 1);
                            if ($details) {
                                $this->sendEmailToProvider($details, $provider['provider_id'], $partner['partner_id'], $date);
                                foreach ($details as $detail) {
                                    if (!ProviderNotification::find()->where(['order_date' => $date, 'provider_id' => $provider['provider_id'], 'product_id' => $detail['product_id']])->exists()) {
                                        $notif = new ProviderNotification;
                                        $notif->order_date = $date;
                                        $notif->provider_id = $provider['provider_id'];
                                        $notif->product_id = $detail['product_id'];
                                        $notif->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $dataProvider = Order::getProvidersOrder($date, 1);
        $this->sendEmailToAdmin($dataProvider, $date);
        
        $partners = Order::getPartnerIdByDate($date, 1);
        if ($partners) {
            foreach ($partners as $partner) {
                $dataProvider = Order::getProviderOrderByPartner($partner['partner_id'], $date, 1);
                $this->sendEmailToPartner($dataProvider, $date, $partner['partner_id']);
            }
        }
        
        $dateEnd = date('Y-m-d 21:00:00');
        $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m'), date('d') - 1, date('Y')));
        $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0);
        $this->sendStockEmailToAdmin($dataProvider, ['start' => $dateStart, 'end' => $dateEnd]);
    }
    
    protected function sendEmailToProvider($details, $provider_id, $partner_id, $date)
    {
        $provider = Provider::find()->where(['id' => $provider_id])->with('user')->one();
        $partner = Partner::findOne($partner_id);
        Yii::$app->mailer->compose('provider/order', [
                'details' => $details,
                'partner' => $partner,
                'date' => $date
            ])
            ->setFrom(Yii::$app->params['fromEmail'])
            ->setTo($provider->user->email)
            ->setSubject('Поступил заказ с сайта "' . Yii::$app->params['name'] . '"')
            ->send();
    }
    
    protected function sendEmailToAdmin($dataProvider, $date)
    {
        if ($emails = NoticeEmail::getEmails()) {
            foreach ($emails as $email) {
                Yii::$app->mailer->compose('admin/order', [
                        'dataProvider' => $dataProvider,
                        'date' => $date,
                        'link' => 'http://vsemdostupno.ru' . '/admin/provider-order/date?' . 'date=' . date('Y-m-d', strtotime($date))
                    ])
                    ->setFrom(Yii::$app->params['fromEmail'])
                    ->setTo($email)
                    ->setSubject('Завершён сбор заявок на поставку с сайта "' . Yii::$app->params['name'] . '"')
                    ->send();
            }
            
        }
    }
    
    protected function sendEmailToPartner($dataProvider, $date, $partner_id)
    {
        $partner = Partner::find()->where(['id' => $partner_id])->with('user')->one();
        Yii::$app->mailer->compose('partner/order', [
                'dataProvider' => $dataProvider,
                'date' => $date
            ])
            ->setFrom(Yii::$app->params['fromEmail'])
            ->setTo($partner->user->email)
            ->setSubject('Завершён сбор заявок на поставку с сайта "' . Yii::$app->params['name'] . '"')
            ->send();
    }
    
    protected function sendStockEmailToAdmin($dataProvider, $date)
    {
        if ($emails = NoticeEmail::getEmails()) {
            foreach ($emails as $email) {
                Yii::$app->mailer->compose('admin/order-stock', [
                        'dataProvider' => $dataProvider,
                        'date' => $date,
                        'link' => 'www.vsemdostupno.ru/admin/order/date?' . 'date=' . date('Y-m-d', strtotime($date['end']))
                    ])
                    ->setFrom(Yii::$app->params['fromEmail'])
                    ->setTo($email)
                    ->setSubject('Завершён рабочий период сбора заявок на "' . date('d.m.Y', strtotime($date['end'])) . '"')
                    ->send();
            }
        }
    }
}