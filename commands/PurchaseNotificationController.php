<?php
namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Archive;
use app\models\Email;
use app\models\User;
use app\models\Provider;
use app\models\Partner;
use app\models\Fund;
use app\models\Account;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
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
        $config = require(__DIR__ . '/../config/constants.php');
        $web = $config['WEB'];
        $token = $config['BOT_TOKEN'];
        $master = Yii::$app->params['masterChatId'];         
        // $admin = $master; 
        $admin = Yii::$app->params['adminChatId'];
        $bot = new Bot($token);        

        $date = date('Y-m-d'); // $date = '2021-11-01';

        $products = PurchaseProduct::find()->where(['<=', 'stop_date', $date])->andWhere(['status' => 'advance'])->all();         
        if ($products) {
            $orders_to_send = [];
            // foreach ($products as $product) {
            for($idx = 0; $idx < count($products); $idx++) {
                $product = $products[$idx];

                $product_total = PurchaseOrderProduct::getProductTotal($product->id);
                
                $provider = Provider::findOne($product->provider_id);

                $feature_id = $product->product_feature_id;
                $product_feature = ProductFeature::findOne($feature_id);
                $real_product_id = $product_feature->product_id;
                $real_product = Product::findOne($real_product_id);
                $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $real_product_id]);
                $category_id = $categoryHasProduct->category_id;
                $category = Category::findOne($category_id);
                
                // если набралось достаточное количество покупателей
                if (!empty($product_total) && $product_total >= $product->purchase_total) {
                    $product->status = 'held';
                    $product->save();
                    
                    $order_products = PurchaseOrderProduct::find()->where(['purchase_product_id' => $product->id])->all();
                    // foreach ($order_products as $order_product) {
                    for($index = 0; $index < count($order_products); $index++) {
                        $order_product = $order_products[$index];

                        if ($order_product->status == 'held') continue;
                        $order_product->status = 'held';
                        $order_product->save();
                        // $fund_balance = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id, 'paid' => 0])->one();
                        $fund_balances = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id, 'paid' => 0])->all();
                        if ($fund_balances) {
                            foreach ($fund_balances as $fund_balance) {
                                $fund_common = Fund::findOne($fund_balance->fund_id);
                                $fund_common->deduction_total += $fund_balance->total;
                                $fund_common->save();
                            }
                        }

                        if (!in_array($order_product->purchase_order_id, $orders_to_send) && !in_array("_".$order_product->purchase_order_id, $orders_to_send)) {
                            $orders_to_send[] = $order_product->purchase_order_id;
                        } 
                    }
                    
                    foreach ($order_products as $order_product) {
                        PurchaseOrder::setOrderStatus($order_product->purchase_order_id);
                    }
                    
                    // foreach ($orders_to_send as $purchase_order_id) {
                    for($i = 0; $i < count($orders_to_send); $i++) {
                        if ($orders_to_send[$i][0] == "_") continue;                        
                        // $bot->sendMessage($master, "----- held_order_member -----" );                        
                        $order = PurchaseOrder::findOne($orders_to_send[$i]);
                        
                        $userOne = User::findOne(['email' => $order->email]);
                        if ($userOne->tg_id && $product->purchase_total > 0) {
                            Email::tg_send('held_order_member', $userOne->tg_id, [ 
                            // Email::tg_send('held_order_member', $master, [ 
                                'fio' => $order->firstname . ' ' . $order->patronymic,
                                'created_at' => date('d.m.Y', strtotime($order->created_at)),
                                // 'order_number' => $order->order_number_copy,
                                // 'order_number' => $order->order_number,
                                'order_number' => "ПР/".date('d.m')."-".$order->id,
                                'order_id' => $order->order_id ? sprintf("%'.05d\n", $order->order_id) : "",
                                'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                                'purchase_date' => date('d.m.Y', strtotime($product->purchase_date))
                            ]);
                        }else {
                            // $bot->sendMessage($master, "----- нет tg_id -----" );
                        }
                        $orders_to_send[$i] = "_".$orders_to_send[$i];
                    }

                    // для ручного управления датами закупок
                    $new_product = new PurchaseProduct;
                    $new_product->created_date = $product->created_date;
                    $new_product->purchase_date = $product->purchase_date;
                    $new_product->stop_date = $product->stop_date;
                    $new_product->renewal = $product->renewal;
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
                    $new_product->status = 'abortive';
                    $new_product->copy = $product->id;
                  
                    // если у провайдера выключено ручное управление датами закупок и включено автопродление дат
                    if ( ! $provider->purchases_management && $product->renewal) {
                        $new_product->created_date = $date;
                        $new_product->purchase_date = date('Y-m-d', strtotime($product->purchase_date) + (strtotime($product->stop_date) - strtotime($product->created_date)));
                        $new_product->stop_date = date('Y-m-d', (strtotime($product->stop_date) + (strtotime($product->stop_date) - strtotime($product->created_date))));
                        $new_product->status = 'advance';
                    }
                        
                    $new_product->save();
                    
                    // если у провайдера выключено ручное управление датами закупок и установлен флаг оповещения провайдера
                    if ( ! $provider->purchases_management && $product->send_notification && $product->purchase_total > 0) {
                        $partners = PurchaseOrder::getPartnerIdByProvider($product->purchase_date, $product->provider_id);
                        if ($partners) {
                            foreach ($partners as $partner) {
                                $details = PurchaseOrder::getOrderDetailsByProviderPartner($product->purchase_date, $product->provider_id, $partner['partner_id']);
                                $this->sendEmailToProvider($details, $product->provider_id, $partner['partner_id'], $product->purchase_date);
                            }
                        }
                    }

                } else { // если НЕ набралось достаточное количество покупателей
                    $product->status = 'abortive';
                    $product->save();
                    
                    $order_products = PurchaseOrderProduct::find()->where(['purchase_product_id' => $product->id])->all();
                    // возврат средств всем покупателям (если таковые имеются)
                    foreach ($order_products as $order_product) {
                        $order_product->status = 'abortive';
                        $order_product->save();
                        $deposit = $order_product->purchaseOrder->user->deposit;
                        // $fund_balance = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id])->one();
                        $fund_balances = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $order_product->id])->all();
                        $fund_balances_total = 0;
                        if ($fund_balances) {
                            foreach ($fund_balances as $fund_balance) {
                                Account::swap(null, $deposit, $fund_balance->total, 'Возврат членского взноса', false);
                                $fund_balances_total += $fund_balance->total;
                            }
                        }
                        $provider_balance = PurchaseProviderBalance::find()->where(['purchase_order_product_id' => $order_product->id])->one();
                        if ($provider_balance) {
                            $provider_account = Account::findOne(['user_id' => $provider_balance->provider->user_id]);
                            Account::swap($provider_account, $deposit, $provider_balance->total, 'Возврат пая по заявке №' . $order_product->purchaseOrder->order_number, false);
                        }
                        
                        if (!in_array($order_product->purchase_order_id, $orders_to_send) && !in_array("_".$order_product->purchase_order_id, $orders_to_send)) {
                            $orders_to_send[] = $order_product->purchase_order_id;
                        }

                        if ($provider_account->user->tg_id) {                             
                            // $bot->sendMessage($master, "----- account-log -----" );                            
                            Email::tg_send('account-log', $provider_account->user->tg_id, [
                            // Email::tg_send('account-log', $master, [
                                'typeName' => $provider_account->typeName,
                                'message' => 'Списан возврат от закупки',
                                'amount' => -$provider_balance->total,
                                'total' => $provider_account->total,
                            ]);
                        }
                        
                        if ($deposit->user->tg_id) {                             
                            // $bot->sendMessage($master, "----- account-log -----" );                            
                            Email::tg_send('account-log', $deposit->user->tg_id, [
                            // Email::tg_send('account-log', $master, [
                                'typeName' => $deposit->typeName,
                                'message' => 'Зачислен возврат от закупки',
                                'amount' => $provider_balance->total + $fund_balances_total,
                                'total' => $deposit->total,
                            ]); 
                        }
                        
                    }
                    
                    // если у провайдера выключено ручное управление датами закупок и включено автопродление дат
                    if (! $provider->purchases_management && $product->renewal) {
                        $new_product = new PurchaseProduct;
                        $new_product->created_date = $date;
                        $new_product->purchase_date = date('Y-m-d', strtotime($product->purchase_date) + (strtotime($product->stop_date) - strtotime($product->created_date)));
                        $new_product->stop_date = date('Y-m-d', (strtotime($product->stop_date) + (strtotime($product->stop_date) - strtotime($product->created_date))));
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

                        $product->delete();
                    }

                    // if ($product->purchase_total > 0) { // тут не надо отключать!!!
                        foreach ($orders_to_send as &$val) {
                            if ($val[0] == "_") continue;   
                            // $bot->sendMessage($master, "----- abortive_order_member -----" );
                            $order = PurchaseOrder::findOne($val);
                            
                            $userOne = User::findOne(['email' => $order->email]);
                            if ($userOne->tg_id) {
                                Email::tg_send('abortive_order_member', $userOne->tg_id, [
                                // Email::tg_send('abortive_order_member', $master, [
                                    'fio' => $order->firstname . ' ' . $order->patronymic,
                                    'created_at' => date('d.m.Y', strtotime($order->created_at)),
                                    // 'order_number' => $order->order_number,
                                    'order_number' => "ПР/".date('d.m')."-".$order->id,
                                    'order_products' => $order->getHtmlMemberEmailFormattedInformation($product->purchase_date),
                                    'new_purchase_date' => ($product->renewal && ! $provider->purchases_management) ? ' Новая закупка состоится ' . date('d.m.Y', strtotime($new_product->purchase_date)) : ''
                                ]);
                            }else {
                                // $bot->sendMessage($master, "----- нет tg_id -----" );
                            }                            
                            $val = "_".$val;
                        }
                    // }
                    
                    foreach ($order_products as $order_product) {
                        PurchaseOrder::setOrderStatus($order_product->purchase_order_id);
                    }
                    
                    if ($product->purchase_total > 0) {
                        if ($product->provider->user->tg_id) {
                            // $bot->sendMessage($master, "----- abortive_order_provider -----" );
                            Email::tg_send('abortive_order_provider', $product->provider->user->tg_id, [
                            // Email::tg_send('abortive_order_provider', $master, [
                                'fio' => $product->provider->user->firstname . ' ' . $product->provider->user->patronymic,
                                'purchase_date' => date('d.m.Y', strtotime($product->purchase_date)),
                                'new_purchase_date' => ($product->renewal && ! $provider->purchases_management) ? ' Новый сбор заявок объявлен на ' . date('d.m.Y', strtotime($new_product->purchase_date)) : '',
                                'new_stop_date' => ($product->renewal && ! $provider->purchases_management) ? 'Заранее, ' . date('d.m.Y', strtotime($new_product->stop_date)) . ', мы сообщим Вам о результатах очередного сбора заявок.' : '',
                                'purchase_total' => $product->purchase_total . ' рублей',
                            ]);
                        }
                    }
                    
                }

            } // end foreach


        } // end if ($products)
        
        // оповещение админа о завершившихся закупках
        $purchaseProducts = PurchaseProduct::find()->where(['<=', 'stop_date', $date])->andWhere(['status' => 'abortive'])->all();
        if ($purchaseProducts) {
            $id_providers = [];
            foreach($purchaseProducts as $purchase) {

                $provider = Provider::findOne($purchase->provider_id);
                
                // если у провайдера включено ручное управление датами закупок
                if ($provider->purchases_management) {
                    $yes = false; // есть ли в массиве?
                    foreach($id_providers as $id_provider) {
                        if ($id_provider == $provider->id) $yes = true;
                    }
                    if ( ! $yes ) {
                        $id_providers[] = $provider->id;
                        
                        $feature_id = $purchase->product_feature_id;
                        $product_feature = ProductFeature::findOne($feature_id);
                        $real_product_id = $product_feature->product_id;
                        $real_product = Product::findOne($real_product_id);
                        $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $real_product_id]);
                        $category_id = $categoryHasProduct->category_id;
                        $category = Category::findOne($category_id);
                        
                        $date_timestamp = strtotime($date);
                        $send = date('d.m.Y', $date_timestamp) . "г. окончен срок сбора заявок на " . $category->name . " ";
                        $date_timestamp = strtotime($purchase->purchase_date);
                        $send .= $provider->name . " доставка продукции состоится " . date('d.m.Y', $date_timestamp) . "г."; 
                        
                        $InlineKeyboardMarkup = [
                            'inline_keyboard' => [
                                [
                                    [
                                        'text' => "Назначить новую дату закупки",
                                        'callback_data' => 'editdatepurchase_' . $provider->id
                                    ],
                                ],
                                [
                                    [
                                        'text' => "Изменить цену закупки",
                                        'callback_data' => 'editpricepurchase_' . $provider->id
                                    ],
                                ],
                                [
                                    [
                                        'text' => "Ассортимент",
                                        'callback_data' => 'assortment_' . $provider->id
                                    ],
                                ],
                            ]
                        ];
                        // $bot->sendMessage($master, $send, null, $InlineKeyboardMarkup);
                        $bot->sendMessage($admin, $send, null, $InlineKeyboardMarkup);

                    }
                }
            }
        }

        
        // оповещение админа о свершившихся закупках
        $purchaseProducts = PurchaseProduct::find()->where(['stop_date' => $date])->andWhere(['status' => 'held'])->all();
        if ($purchaseProducts) {
            
            $id_providers = [];
            foreach($purchaseProducts as $purchase) {

                $provider = Provider::findOne($purchase->provider_id);
                
                // если у провайдера включено ручное управление датами закупок
                if ($provider->purchases_management) {
                    $yes = false; // есть ли в массиве?
                    foreach($id_providers as $id_provider) {
                        if ($id_provider == $provider->id) $yes = true;
                    }
                    if ( ! $yes ) {
                        $id_providers[] = $provider->id;

                        $date_timestamp = strtotime($purchase->purchase_date);
                        $send = "Сформирована заявка по товарам ".$provider->name." на ".date('d.m.Y', $date_timestamp)."\r\n";
                        
                        $array = [];
                        foreach($purchaseProducts as $purch_prod) {
                            $order_products = PurchaseOrderProduct::find()
                                ->where(['purchase_product_id' => $purch_prod->id])
                                ->andWhere(['provider_id' => $provider->id])
                                ->all();
                            foreach($order_products as $order_product) {                                
                                $product_feature_id = $order_product->product_feature_id;
                                $purchase_price = $order_product->purchase_price;
                                $quantity = $order_product->quantity;
                                $product_name = $order_product->name;
                                $yes = true;
                                for($i = 0; $i < count($array); $i++) {
                                    if ($array[$i]['product_feature_id'] == $product_feature_id)
                                    {
                                        $yes = false;
                                        $array[$i]['quantity'] += $quantity;
                                    }
                                }
                                if ($yes) $array[] = [
                                    'product_feature_id' => $product_feature_id,
                                    'purchase_price' => $purchase_price,
                                    'quantity' => $quantity,
                                    'name' => $product_name,
                                ];
                            }
                        }

                        $purchase_total_all = 0;
                        $item = 0;
                        foreach($array as $order_product) {
                            $item++;
                            $product_name = $order_product['name'];
                            $quantity = $order_product['quantity'];
                            if ( fmod($quantity, 1) == 0 ) { // если дробная часть равна нулю
                                $quantity = floor($quantity);
                            }
                            $purchase_price = $order_product['purchase_price']; // цена закупки
                            $purchase_total = $purchase_price * $quantity; // итого закупки
                            $purchase_total_all += $purchase_total;

                            $send .= $item.". ".$product_name."–".$quantity."шт. ".$purchase_total."\r\n";
                        }
                        if ( fmod($purchase_total_all, 1) == 0 ) {
                            $send .= "                       Итого: ".$purchase_total_all.".00";
                        }else {
                            $send .= "                       Итого: ".$purchase_total_all;
                        }

                        $InlineKeyboardMarkup = [
                            'inline_keyboard' => [
                                [
                                    [
                                        'text' => "Отправить поставщику",
                                        'callback_data' => 'sendThisTextToTheSupplier_' . $provider->id . '_' . $purchase_total_all . '_' .date('d.m.Y', $date_timestamp)
                                    ],
                                ],
                            ]
                        ];          
                        
                        // $bot->sendMessage($master, "----- оповещение админа о завершившихся закупках -----" );

                        // $bot->sendMessage($master, $send, null, $InlineKeyboardMarkup);
                        $bot->sendMessage($admin, $send, null, $InlineKeyboardMarkup);
                    }
                }
            }
        }

        
        /*
        * Очистка архива (удаляются записи старше 45 дней)
        */
        $archives = Archive::find()->all();
        foreach ($archives as $archive) {
            $archive_date = strtotime($archive->date);
            $now = strtotime($date);
            $difference = $now - $archive_date;
            $hours = $difference / (60 * 60 * 24); // количество дней

            if ($hours > 45) {
                $archive->delete();
            }
        }

         
    }
    
    protected function sendEmailToProvider($details, $provider_id, $partner_id, $date)
    {
        $provider = Provider::find()->where(['id' => $provider_id])->with('user')->one();
        $partner = Partner::findOne($partner_id);
        
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