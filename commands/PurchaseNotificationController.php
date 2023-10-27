<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Email;
use app\models\User;
use app\models\Provider;
use app\models\Partner;
use app\models\Fund;
use app\models\Account;
use app\modules\purchase\models\PurchaseProduct;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseFundBalance;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\bots\api\Bot;


class PurchaseNotificationController extends Controller
{
    public function actionIndex()
    {            
        $date = date('Y-m-d');
        // $date = '2021-11-01';
         $products = PurchaseProduct::find()->where(['<=', 'stop_date', $date])->andWhere(['status' => 'advance'])->all();
        // $products = PurchaseProduct::find()->where(['stop_date' => $date, 'status' => 'advance'])->all();
        // $products = PurchaseProduct::find()->where(['stop_date' => $date])->andWhere(['status' => 'advance'])->all();
        if ($products) {
            foreach ($products as $product) {
                $orders_to_send = [];
                $product_total = PurchaseOrderProduct::getProductTotal($product->id);

                if (!empty($product_total) && $product_total >= $product->purchase_total) {
                    $product->status = 'held';
                    $product->save();
                    
                    $order_products = PurchaseOrderProduct::find()->where(['purchase_product_id' => $product->id])->all();
                    foreach ($order_products as $order_product) {
                        $order_product->status = 'held';
                        $order_product->save();
                        $fund_balance = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id, 'paid' => 0])->one();
                        if ($fund_balance) {
                            $fund_common = Fund::findOne($fund_balance->fund_id);
                            $fund_common->deduction_total += $fund_balance->total;
                            $fund_common->save();
                        }
                        if (!in_array($order_product->purchase_order_id, $orders_to_send)) {
                            $orders_to_send[] = $order_product->purchase_order_id;
                        } 
                    }
                    
                    foreach ($order_products as $order_product) {
                        PurchaseOrder::setOrderStatus($order_product->purchase_order_id);
                    }
                    
                    foreach ($orders_to_send as $val) {
                        $order = PurchaseOrder::findOne($val);
                        // try {
                        //     Email::send('held_order_member', $order->email, [
                        //         'fio' => $order->firstname . ' ' . $order->patronymic,
                        //         'created_at' => date('d.m.Y', strtotime($order->created_at)),
                        //         'order_number' => $order->order_number_copy,
                        //         'order_id' => sprintf("%'.05d\n", $order->order_id),
                        //         'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                        //         'purchase_date' => date('d.m.Y', strtotime($product->purchase_date))
                        //     ]);
                        // } catch (Exception $e) {
                        //     unset($e);
                        // }
                        $userOne = User::findOne(['email' => $order->email]);
                        if ($userOne->tg_id) {
                            Email::tg_send('held_order_member', $userOne->tg_id, [
                                'fio' => $order->firstname . ' ' . $order->patronymic,
                                'created_at' => date('d.m.Y', strtotime($order->created_at)),
                                'order_number' => $order->order_number_copy,
                                'order_id' => sprintf("%'.05d\n", $order->order_id),
                                'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                                'purchase_date' => date('d.m.Y', strtotime($product->purchase_date))
                            ]);
                        }
                    }
                    
                    if ($product->renewal) {
                        $new_product = new PurchaseProduct;

                        $new_product->created_date = $date;

                        $new_product->purchase_date = date('Y-m-d', strtotime($product->purchase_date) + (strtotime($product->stop_date) - strtotime($product->created_date)));

                        $new_product->stop_date = date('Y-m-d', (strtotime($product->stop_date) + (strtotime($product->stop_date) - strtotime($product->created_date))));

                        // $new_product->created_date = date('Y-m-d', strtotime($product->created_date) + (strtotime($product->purchase_date) - strtotime($product->created_date)));

                        // $new_product->stop_date = date('Y-m-d', (strtotime($product->purchase_date) + (strtotime($product->purchase_date) - strtotime($product->created_date))) - (strtotime($product->purchase_date) - strtotime($product->stop_date)));

                        $new_product->renewal = 1;
                        $new_product->purchase_total = $product->purchase_total;
                        $new_product->is_weights = $product->is_weights;
                        $new_product->tare = $product->tare;
                        $new_product->weight = $product->weight;
                        $new_product->measurement = $product->measurement;
                        $new_product->summ = $product->summ;
                        $new_product->product_feature_id = $product->product_feature_id;
                        $new_product->provider_id = $product->provider_id;
                        $new_product->comment = $product->comment;
                        $new_product->send_notification = $product->send_notification;
                        $new_product->status = 'advance';
                        $new_product->copy = $product->id;
                        $new_product->save();
                    }
                    
                    if ($product->send_notification) {
                        $partners = PurchaseOrder::getPartnerIdByProvider($product->purchase_date, $product->provider_id);
                        if ($partners) {
                            foreach ($partners as $partner) {
                                $details = PurchaseOrder::getOrderDetailsByProviderPartner($product->purchase_date, $product->provider_id, $partner['partner_id']);
                                $this->sendEmailToProvider($details, $product->provider_id, $partner['partner_id'], $product->purchase_date);
                            }
                        }
                    }

                } else {
                    $product->status = 'abortive';
                    $product->save();
                    
                    $order_products = PurchaseOrderProduct::find()->where(['purchase_product_id' => $product->id])->all();
                    foreach ($order_products as $order_product) {
                        $order_product->status = 'abortive';
                        $order_product->save();
                        $deposit = $order_product->purchaseOrder->user->deposit;
                        $fund_balance = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id])->one();
                        $provider_balance = PurchaseProviderBalance::find()->where(['purchase_order_product_id' => $order_product->id])->one();
                        if ($fund_balance) {
                            Account::swap(null, $deposit, $fund_balance->total, 'Возврат членского взноса', false);
                        }
                        if ($provider_balance) {
                            $provider_account = Account::findOne(['user_id' => $provider_balance->provider->user_id]);
                            Account::swap($provider_account, $deposit, $provider_balance->total, 'Возврат пая по заявке №' . $order_product->purchaseOrder->order_number, false);
                        }
                        
                        if (!in_array($order_product->purchase_order_id, $orders_to_send)) {
                            $orders_to_send[] = $order_product->purchase_order_id;
                        }

                        // try {
                        //     Email::send('account-log', $provider_account->user->email, [
                        //         'typeName' => $provider_account->typeName,
                        //         'message' => 'Списан возврат от закупки',
                        //         'amount' => -$provider_balance->total,
                        //         'total' => $provider_account->total,
                        //     ]);
                        // } catch (Exception $e) {
                        //     unset($e);
                        // }
                        if ($provider_account->user->tg_id) { 
                            Email::tg_send('account-log', $provider_account->user->tg_id, [
                                'typeName' => $provider_account->typeName,
                                'message' => 'Списан возврат от закупки',
                                'amount' => -$provider_balance->total,
                                'total' => $provider_account->total,
                            ]);
                        }
                        
                        // try {
                        //     Email::send('account-log', $deposit->user->email, [
                        //         'typeName' => $deposit->typeName,
                        //         'message' => 'Зачислен возврат от закупки',
                        //         'amount' => $provider_balance->total + $fund_balance->total,
                        //         'total' => $deposit->total,
                        //     ]); 
                        // } catch (Exception $e) {
                        //     unset($e);
                        // }
                        if ($deposit->user->tg_id) { 
                            Email::tg_send('account-log', $deposit->user->tg_id, [
                                'typeName' => $deposit->typeName,
                                'message' => 'Зачислен возврат от закупки',
                                'amount' => $provider_balance->total + $fund_balance->total,
                                'total' => $deposit->total,
                            ]); 
                        }
                        
                    }
                    
                    if ($product->renewal) {
                        $new_product = new PurchaseProduct;

                        $new_product->created_date = $date;

                        $new_product->purchase_date = date('Y-m-d', strtotime($product->purchase_date) + (strtotime($product->stop_date) - strtotime($product->created_date)));

                        $new_product->stop_date = date('Y-m-d', (strtotime($product->stop_date) + (strtotime($product->stop_date) - strtotime($product->created_date))));

                        // $new_product->created_date = date('Y-m-d', strtotime($product->created_date) + (strtotime($product->purchase_date) - strtotime($product->created_date)));

                        // $new_product->stop_date = date('Y-m-d', (strtotime($product->purchase_date) + (strtotime($product->purchase_date) - strtotime($product->created_date))) - (strtotime($product->purchase_date) - strtotime($product->stop_date)));

                        $new_product->renewal = 1;
                        $new_product->purchase_total = $product->purchase_total;
                        $new_product->is_weights = $product->is_weights;
                        $new_product->tare = $product->tare;
                        $new_product->weight = $product->weight;
                        $new_product->measurement = $product->measurement;
                        $new_product->summ = $product->summ;
                        $new_product->product_feature_id = $product->product_feature_id;
                        $new_product->provider_id = $product->provider_id;
                        $new_product->comment = $product->comment;
                        $new_product->send_notification = $product->send_notification;
                        $new_product->status = 'advance';
                        $new_product->copy = $product->id;
                        $new_product->save();
                    }
                    
                    foreach ($orders_to_send as $val) {
                        $order = PurchaseOrder::findOne($val);
                        // try {
                        //     Email::send('abortive_order_member', $order->email, [
                        //         'fio' => $order->firstname . ' ' . $order->patronymic,
                        //         'created_at' => date('d.m.Y', strtotime($order->created_at)),
                        //         'order_number' => $order->order_number,
                        //         'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                        //         'new_purchase_date' => $product->renewal ? ' Новая закупка состоится ' . date('d.m.Y', strtotime($new_product->purchase_date)) : ''
                        //     ]);
                        // } catch (Exception $e) {
                        //     unset($e);
                        // }
                        $userOne = User::findOne(['email' => $order->email]);
                        if ($userOne->tg_id) {
                            Email::tg_send('abortive_order_member', $userOne->tg_id, [
                                'fio' => $order->firstname . ' ' . $order->patronymic,
                                'created_at' => date('d.m.Y', strtotime($order->created_at)),
                                'order_number' => $order->order_number,
                                'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                                'new_purchase_date' => $product->renewal ? ' Новая закупка состоится ' . date('d.m.Y', strtotime($new_product->purchase_date)) : ''
                            ]);
                        }
                    }
                    
                    foreach ($order_products as $order_product) {
                        PurchaseOrder::setOrderStatus($order_product->purchase_order_id);
                    }
                    // try {
                    //     Email::send('abortive_order_provider', $product->provider->user->email, [
                    //         'fio' => $product->provider->user->firstname . ' ' . $product->provider->user->patronymic,
                    //         'purchase_date' => date('d.m.Y', strtotime($product->purchase_date)),
                    //         'new_purchase_date' => $product->renewal ? ' Новый сбор заявок объявлен на ' . date('d.m.Y', strtotime($new_product->purchase_date)) : '',
                    //         'new_stop_date' => $product->renewal ? 'Заранее, ' . date('d.m.Y', strtotime($new_product->stop_date)) . ', мы сообщим Вам о результатах очередного сбора заявок.' : '',
                    //         'purchase_total' => $product->purchase_total . ' рублей',
                    //     ]);
                    // } catch (Exception $e) {
                    //     unset($e);
                    // }
                    if ($product->provider->user->tg_id) {
                        Email::tg_send('abortive_order_provider', $product->provider->user->tg_id, [
                            'fio' => $product->provider->user->firstname . ' ' . $product->provider->user->patronymic,
                            'purchase_date' => date('d.m.Y', strtotime($product->purchase_date)),
                            'new_purchase_date' => $product->renewal ? ' Новый сбор заявок объявлен на ' . date('d.m.Y', strtotime($new_product->purchase_date)) : '',
                            'new_stop_date' => $product->renewal ? 'Заранее, ' . date('d.m.Y', strtotime($new_product->stop_date)) . ', мы сообщим Вам о результатах очередного сбора заявок.' : '',
                            'purchase_total' => $product->purchase_total . ' рублей',
                        ]);
                    }
                    
                }
            }
        }
    }
    
    protected function sendEmailToProvider($details, $provider_id, $partner_id, $date)
    {
        $provider = Provider::find()->where(['id' => $provider_id])->with('user')->one();
        $partner = Partner::findOne($partner_id);
        // try {
        //     Yii::$app->mailer->compose('provider/order', [
        //             'details' => $details,
        //             'partner' => $partner,
        //             'date' => $date
        //         ])
        //         ->setFrom(Yii::$app->params['fromEmail'])
        //         ->setTo($provider->user->email)
        //         ->setSubject('Поступил заказ с сайта "' . Yii::$app->params['name'] . '"')
        //         ->send();
        // } catch (Exception $e) {
        //     unset($e);
        // }
        
        if ($provider->user->tg_id) {
            $config = require(__DIR__ . '/../config/constants.php');
            $web = $config['WEB'];
            $token = $config['BOT_TOKEN'];
            $master = Yii::$app->params['masterChatId'];         
            // $admin = $master; 
            $admin = Yii::$app->params['adminChatId'];
            $bot = new Bot($token);

            // $bot->sendMessage($master,"sendEmailToProvider");

            $total_price = 0;
            $send = "<h4>Поступил заказ от ". $partner->name . " для поставки товаров на ". date('d.m.Y', strtotime($date)) . "</h4>";
            $send .= "<table border='1'>";
            $send .= "<tr><th>Заказчик</th><th>№ п/п</th><th>Наименование товаров</th><th>Количество</th><th>На сумму</th></tr>";
            $rowspan = count($details);
            if ($rowspan == 1) {
                $send .= "<tr><td>". $partner->name . "<br />" . $partner->address . "</td>";
                $send .= "<td>1</td><td>". $details[0]['product_name'] . ", " . $details[0]['product_feature_name']. "</td>";                
                $send .= "<td>". number_format($details[0]['quantity']) . "</td><td>". number_format($details[0]['total'], 2, ".", " ")."</td>";
                $send .= "</tr>". $total_price += $details[0]['total'];
            }else {
                $send .= "<tr><td rowspan='". $rowspan. "'>". $partner->name . "<br />" . $partner->address . "</td><td>1</td>";
                $send .= "<td>" . $details[0]['product_name'] . ", " . $details[0]['product_feature_name'] ."</td>";
                $send .= "<td>" . number_format($details[0]['quantity']) ."</td>";
                $send .= "<td>". number_format($details[0]['total'], 2, ".", " ") ."</td></tr>";
                $total_price += $details[0]['total'];
                foreach ($details as $k => $detail) {
                    if ($k != 0) {
                        $send .= "<tr><td>". ($k + 1)."</td><td>". $detail['product_name'] . ", " . $detail['product_feature_name']."</td>";
                        $send .= "<td>". number_format($detail['quantity'])."</td><td>". number_format($detail['total'], 2, ".", " ")."</td>";
                        $send .= "</tr>". $total_price += $detail['total'];
                    }
                }
            }
            $send .= "<tr><td colspan='5' align='right'><b>ИТОГО: ". number_format($total_price, 2, ".", ""). "</b></td></tr>";
            $send .= "</table>";

            // $bot->sendMessage($master, $send);
            $bot->sendMessage($provider->user->tg_id, $send);
        }
    }
}