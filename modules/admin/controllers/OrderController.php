<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use app\models\Email;
use app\models\Order;
use app\models\User;
use app\models\Product;
use app\models\OrderHasProduct;
use app\models\Template;
use app\models\OrderStatus;
use app\models\Account;
use app\models\Member;
use app\models\ProviderHasProduct;
use app\models\ProviderStock;
use app\models\Provider;
use app\models\StockBody;
use app\models\ProductFeature;
use app\models\Fund;
use app\models\OView;
use app\models\Partner;
use app\models\NoticeEmail;
use app\modules\admin\models\OrderForm;
use app\helpers\Sum;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\purchase\models\PurchaseFundBalance;
use app\modules\purchase\models\PurchaseProduct;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrderController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionPartner()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->where('role = :role', [':role' => User::ROLE_PARTNER]),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'title' => 'Заказы партнеров',
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionMember()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->where('role = :role', [':role' => User::ROLE_MEMBER]),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'title' => 'Заказы участников',
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionGuest()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->where('role IS NULL'),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'title' => 'Заказы гостей',
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete()
    {
        $order = Order::findOne($_POST['id']);

        /*if (!$order->partner_id) {
            $url = 'partner';
        } elseif ($order->partner_id && $order->user_id) {
            $url = 'member';
        } else {
            $url = 'guest';
        }*/

        $order->delete();
        return true;
        //return $this->redirect($url);
    }
    
    public function actionDeleteReturn()
    {
        $order = Order::findOne($_POST['id']);

        /*if (!$order->partner_id) {
            $url = 'partner';
        } elseif ($order->partner_id && $order->user_id) {
            $url = 'member';
        } else {
            $url = 'guest';
        }*/

        $order->deleteReturn();
        return true;

        //return $this->redirect($url);
    }

    public function actionDownloadOrder($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $spelloutTotal = sprintf(
            '%s %02d копеек',
            Yii::t('app', '{value, spellout}', ['value' => floor($order->total)], Yii::$app->language),
            round(100 * ($order->total - floor($order->total)))
        );

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['message'] = sprintf('Основание: Паевой взнос по программе "Стол заказов" - %.2f руб.', $order->total);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue('A25', $parameters['message'])
            ->setCellValue('AM21', $order->total)
            ->setCellValue('AR15', sprintf('%05d', $order->order_id))
            ->setCellValue('BB15', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y'))
            ->setCellValue('BQ10', sprintf('к приходному кассовому ордеру № %05d', $order->order_id))
            ->setCellValue('BQ12', sprintf('от %s г.', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')))
            ->setCellValue('BQ14', $parameters['fullName'])
            ->setCellValue('BQ16', $parameters['message'])
            ->setCellValue('BQ23', $spelloutTotal)
            ->setCellValue('BQ29', sprintf('%s г.', Yii::$app->formatter->asDate($order->created_at, 'php:d.m.Y')))
            ->setCellValue('BV21', floor($order->total))
            ->setCellValue('CM21', sprintf('%02d', round(100 * ($order->total - floor($order->total)))))
            ->setCellValue('F27', $spelloutTotal)
            ->setCellValue('K23', $parameters['fullName']);
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    public function actionDownloadAct($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['orderTotal'] = sprintf('%.2f', $order->total);
        $parameters['orderSubTotal'] = sprintf('%.2f', $order->getProductPriceTotal('purchase_price'));
        $parameters['orderTax'] = sprintf('%.2f', $parameters['orderTotal'] - $parameters['orderSubTotal']);

        $objectExcel->setActiveSheetIndex(0)
            ->insertNewRowBefore(15, count($order->orderHasProducts));

        foreach ($order->orderHasProducts as $count => $orderHasProduct) {
            $cellName = 'A' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->name);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $cellName = 'C' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->purchase_price);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $cellName = 'D' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->quantity);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $cellName = 'F' . (15 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->purchase_price * $orderHasProduct->quantity);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getFont()->setBold(false);
            $objectExcel->setActiveSheetIndex(0)
                ->getStyle($cellName)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }

        $cellName = 'E' . (15 + count($order->orderHasProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderSubTotal']);

        $cellName = 'B' . (18 + count($order->orderHasProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderTax']);

        $cellName = 'E' . (23 + count($order->orderHasProducts));
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['orderTotal']);

        $cellNumbers = [5, 9, 10, 11];
        foreach ($cellNumbers as $cellNumber) {
            $value = $objectExcel->setActiveSheetIndex(0)->getCell('A' . $cellNumber)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue('A' . $cellNumber, Template::parseTemplate($parameters, $value));
        }

        $cellNumbers = [32];
        foreach ($cellNumbers as $cellNumber) {
            $cellNumber += count($order->orderHasProducts);
            $value = $objectExcel->setActiveSheetIndex(0)->getCell('A' . $cellNumber)->getValue();
            $objectExcel->setActiveSheetIndex(0)->setCellValue('A' . $cellNumber, Template::parseTemplate($parameters, $value));
        }

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    public function actionDownloadRequest($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);

        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $spelloutTotal = sprintf(
            '%s %02d копеек',
            Yii::t('app', '{value, spellout}', ['value' => floor($order->total)], Yii::$app->language),
            round(100 * ($order->total - floor($order->total)))
        );

        $parameters = Template::getUserParameters($order->user ? $order->user : new User());
        $parameters['orderTotal'] = sprintf('%.2f', $order->total);
        $parameters['orderSubTotal'] = sprintf('%.2f', $order->getProductPriceTotal('purchase_price'));
        $parameters['orderTax'] = sprintf('%.2f', $parameters['orderTotal'] - $parameters['orderSubTotal']);
        $parameters['quantityTotal'] = 0;

        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', sprintf('ЗАКАЗ № %05d от %s', $order->order_id, $parameters['currentDate']))
            ->setCellValue('C8', $order->fullName)
            ->setCellValue('A15', sprintf('Итого к оплате: %s', $spelloutTotal));

        $objectExcel->setActiveSheetIndex(0)
            ->insertNewRowBefore(12, count($order->orderHasProducts) - 1);

        foreach ($order->orderHasProducts as $count => $orderHasProduct) {
            $objectExcel->setActiveSheetIndex(0)
                ->mergeCells('B' . (11 + $count) . ':' . 'E' . (11 + $count));
            $cellName = 'A' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, 1 + $count);
            $cellName = 'B' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->name);
            $cellName = 'F' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->price);
            $cellName = 'G' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->quantity);
            $parameters['quantityTotal'] += $orderHasProduct->quantity;
            $cellName = 'I' . (11 + $count);
            $objectExcel->setActiveSheetIndex(0)
                ->setCellValue($cellName, $orderHasProduct->total);
        }

        $cellName = 'G' . (12 + $count);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $parameters['quantityTotal']);

        $cellName = 'I' . (12 + $count);
        $objectExcel->setActiveSheetIndex(0)
            ->setCellValue($cellName, $order->total);

        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }

    public function actionCreate()
    {
        $model = new OrderForm();
        $total_paid_for_provider = 0;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $is_purchase = isset($_POST['is_purchase']);
            $user = User::findOne($model->user_id);
            $productList = Json::decode($model->product_list);
            $productList = ArrayHelper::map($productList, 'id', 'quantity');

            if ($is_purchase) {
                $products = PurchaseProduct::find()
                    ->joinWith('productFeature')
                    ->joinWith('productFeature.product')
                    ->joinWith('productFeature.productPrices')
                    ->where(['IN', 'purchase_product.id', array_keys($productList)])
                    ->all();
            } else {
                $products = ProductFeature::find()
                    ->joinWith('product')
                    ->joinWith('productPrices')
                    ->where(['IN', 'product_feature.id', array_keys($productList)])
                    ->all();
            }
            
            foreach ($products as $index => $product) {
                if ($is_purchase) {
                    $products[$index] = $product->productFeature;
                    $products[$index]->purchase_product_id = $product->id;
                }
                $products[$index]->cart_quantity = $productList[$product->id];
            }
            
            $total = 0;
            foreach ($products as $product) {
                if ($product->is_weights == 1) {
                    $total += $product->cart_quantity * $product->volume * $product->productPrices[0]->member_price;
                } else {
                    $total += $product->cart_quantity * $product->productPrices[0]->member_price;
                }
            }

            if ($total > $user->deposit->total) {
                throw new ForbiddenHttpException('Недостаточно средств на счете для совершения покупки!');
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($is_purchase) {
                    $order = new PurchaseOrder;
                } else {
                    $order = new Order();
                }
                
                $order->email = $user->email;
                $order->phone = $user->phone;
                $order->firstname = $user->firstname;
                $order->lastname = $user->lastname;
                $order->patronymic = $user->patronymic;
                $order->comment = 'Заказ сделан через административную панель.';
                $order->paid_total = $total;
                $order->total = $total;

                if ($user->member) {
                    $partner = $user->member->partner;
                    $order->partner_id = $partner->id;
                    $order->partner_name = $partner->name;
                } elseif ($user->partner) {
                    $partner = $user->partner;
                }
                $order->city_id = $partner->city->id;
                $order->city_name = $partner->city->name;

                $order->user_id = $user->id;
                $order->role = $user->role;
                if ($user->role == User::ROLE_PROVIDER) {
                    $member = Member::find()->where(['user_id' => $user->id])->one();
                    if ($member) {
                        $order->role = User::ROLE_MEMBER;
                    }
                }

                if (!$is_purchase) {
                    $orderStatus = OrderStatus::findOne(['type' => OrderStatus::STATUS_NEW]);
                    $order->order_status_id = $orderStatus->id;
                }

                if (!$order->save()) {
                    throw new Exception('Ошибка сохранения заказа!');
                }

                foreach ($products as $product) {
                    if (!$product->quantity && $product->product->orderDate && (strtotime($product->product->orderDate) + strtotime('1 day', 0)) < time()) {
                        throw new Exception('"' . $product->product->name . '" нельзя заказать!');
                    }

                    if (!$is_purchase) {
                        if (isset($product->quantity)) {
                            if ($product->is_weights == 1) {
                                $product->quantity -= $product->volume * $product->cart_quantity;
                            } else {
                                $product->quantity -= $product->cart_quantity;
                            }

                            if ($product->quantity < 0) {
                                throw new Exception('Ошибка обновления количества товара в магазине!');
                            }
                            
                            if (!$product->save()) {
                                throw new Exception('Ошибка обновления количества товара в магазине!');
                            }
                        }
                    }

                    if ($is_purchase) {
                        $orderHasProduct = new PurchaseOrderProduct;
                        $orderHasProduct->purchase_order_id = $order->id;
                        $orderHasProduct->purchase_product_id = $product->purchase_product_id;
                        $orderHasProduct->status = 'advance';
                    } else {
                        $orderHasProduct = new OrderHasProduct();
                        $orderHasProduct->order_id = $order->id;
                    }
                    
                    $orderHasProduct->product_id = $product->product_id;
                    $orderHasProduct->name = $product->product->name;
                    
                    if (!$is_purchase) {
                        $orderHasProduct->orderDate = $product->product->orderDate;
                        $orderHasProduct->purchaseDate = $product->product->purchaseDate;
                        $orderHasProduct->storage_price = 0;
                        $orderHasProduct->invite_price = 0;
                        $orderHasProduct->fraternity_price = 0;
                        $orderHasProduct->group_price = 0;
                        $orderHasProduct->purchase = 0;
                    }
                    
                    $orderHasProduct->price = $product->productPrices[0]->member_price;
                    $orderHasProduct->purchase_price = $product->purchase_price;
                    $orderHasProduct->product_feature_id = $product->id;
                    
                    if ($product->is_weights == 1) {
                        $orderHasProduct->quantity = $product->volume * $product->cart_quantity;
                    } else {
                        $orderHasProduct->quantity = $product->cart_quantity;
                    }
                    $orderHasProduct->total = $orderHasProduct->quantity * $product->productPrices[0]->member_price;
                    
                    $provider = ProviderHasProduct::find()->where(['product_id' => $product->product_id])->one();
                    $provider_id = $provider ? $provider->provider_id : 0;
                    if ($provider_id != 0) {
                        $orderHasProduct->provider_id = $provider_id;
                        
                        $provider_model = Provider::findOne(['id' => $provider_id]);
                        $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);
                        
                        if (!$is_purchase) {
                            $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);

                            if ($stock_provider && !$product->product->isPurchase()) {
                                if ($stock_provider->reaminder_rent >= $orderHasProduct->quantity) {
                                    $stock_provider->reaminder_rent -= $orderHasProduct->quantity;
                                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                    $paid_for_provider = $orderHasProduct->quantity * $body->summ;
                                    $stock_provider->summ_on_deposit += $paid_for_provider;
                                    $stock_provider->save();
                                } else {
                                    $rest = $orderHasProduct->quantity - $stock_provider->reaminder_rent;
                                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                    $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                                    $stock_provider->reaminder_rent = 0;
                                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                    $stock_provider->save();
                                    
                                    while ($rest > 0) {
                                        $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);
                                        
                                        if ($stock_provider->reaminder_rent >= $rest) {
                                            $stock_provider->reaminder_rent -= $rest;
                                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                            $paid_for_provider = $rest * $body->summ;
                                            $stock_provider->summ_on_deposit += $paid_for_provider;
                                            $stock_provider->save();
                                            $rest = 0;
                                        } else {
                                            $rest -= $stock_provider->reaminder_rent;
                                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                                            $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                                            $stock_provider->reaminder_rent = 0;
                                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                                            $stock_provider->save();
                                        }
                                    }
                                }
                                
                                if ($body->deposit == '1') {
                                    $paid_for_provider = $orderHasProduct->quantity * $body->summ;
                                    if (!Account::swap($user->deposit, $provider_account, $paid_for_provider, 'Перевод пая на счёт', false)) {
                                        throw new Exception('Ошибка модификации счета пользователя!');
                                    }
                                    Email::send('account-log', $provider_account->user->email, [
                                        'message' => 'Перевод пая на счёт',
                                        'amount' => $paid_for_provider,
                                        'total' => $provider_account->total,
                                    ]);
                                    $total_paid_for_provider += $paid_for_provider;
                                }
                            }
                        }
                    }
                    
                    if (!$orderHasProduct->save()) {
                        throw new Exception('Ошибка сохранения товара в заказе!');
                    }
                    if ($is_purchase) {
                        $provider_balance = new PurchaseProviderBalance;
                        $provider_balance->provider_id = $provider_id;
                        $provider_balance->user_id = $user->id;
                        $provider_balance->purchase_order_product_id = $orderHasProduct->id;
                        $provider_balance->total = $orderHasProduct->quantity * $orderHasProduct->purchase_price;
                        $provider_balance->save();
                        
                        PurchaseFundBalance::setDeductionForOrder($orderHasProduct->id, $user->id);
                        
                        $total_paid_for_provider += $provider_balance->total;
                        if (!Account::swap($user->deposit, $provider_account, $provider_balance->total, 'Перевод пая на счёт', false)) {
                            throw new Exception('Ошибка модификации счета пользователя!');
                        }
                    }
                }

                if ($order->paid_total > 0) {
                    if ($order->paid_total == $order->total) {
                        //$message = sprintf('Списано по заказу №%s.', $order->id);
                    } else {
                        //$message = sprintf('Частичная списано по заказу №%s.', $order->id);
                    }
                    
                    $message = 'Членский взнос';

                    if (!Account::swap($user->deposit, null, $order->paid_total - $total_paid_for_provider, $message, !$is_purchase)) {
                        throw new Exception('Ошибка модификации счета пользователя!');
                    }
                    if ($user->role == User::ROLE_PROVIDER) {
                        ProviderStock::setStockSum($user->id, $order->paid_total);
                    }
                    
                    if ($is_purchase) {
                        $deposit = $user->deposit;
                        $message = 'Списание на закупку';
                        Email::send('account-log', $deposit->user->email, [
                            'typeName' => $deposit->typeName,
                            'message' => $message,
                            'amount' => -$order->paid_total,
                            'total' => $deposit->total,
                        ]);
                    }
                    
                }
                
                if (!$is_purchase) Fund::setDeductionForOrder($product->id, $product->purchase_price, $product->cart_quantity);

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                throw new ForbiddenHttpException($e->getMessage());
            }

            if (!$is_purchase) {
                $orderId = $order->id;
                $order = Order::findOne($orderId);
                $orderId = sprintf("%'.05d\n", $order->order_id);
                
                if ($emails = NoticeEmail::getEmails()) {
                    Email::send('order-customer', $emails, [
                        'id' => $orderId,
                        'information' => $order->htmlEmailFormattedInformation,
                    ]);
                }

                if ($order->partner) {
                    Email::send('order-partner', $order->partner->email, [
                        'id' => $orderId,
                        'information' => $order->htmlEmailFormattedInformation,
                    ]);
                }

                Email::send('order-customer', $order->email, [
                    'id' => $orderId,
                    'information' => $order->htmlEmailFormattedInformation,
                ]);
            } else {
                Email::send('add_advance_order', $order->email, [
                    'fio' => $user->respectedName,
                    'order_products' => $order->htmlEmailFormattedInformation,
                    'order_number' => $order->order_number,
                ]);
            }
            
            if ($is_purchase) {
                return $this->redirect(['/admin/provider-order']);
            } else {
                return $this->redirect(['/admin/order']);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionDownloadReturnFeeAct($id)
    {
        $order = Order::findOne($id);

        if (!$order) {
            throw new NotFoundHttpException('Заказ не найден.');
        }

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('order', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%d.%s', $templateName, $order->id, $templateExtension);
        
        $objectReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objectExcel = $objectReader->load($templateFile);

        $parameters = Template::getUserParameters($order->user);
        $value_b13 = $objectExcel->setActiveSheetIndex(0)->getCell('B13')->getValue();
        $value_f8 = $objectExcel->setActiveSheetIndex(0)->getCell('F8')->getValue();
        
        $objectExcel->setActiveSheetIndex(0)->setCellValue('T11', $parameters['currentDate']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B13', Template::parseTemplate($parameters, $value_b13));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F8', Template::parseTemplate($parameters, $value_f8));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F4', $parameters['fullName']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F6', $parameters['fullName']);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('N11', sprintf('%05d', $order->order_id));

        $total_summ = 0;
        if (count($order->orderHasProducts) > 1) {
            $objectExcel->setActiveSheetIndex(0)->insertNewRowBefore(20, count($order->orderHasProducts) - 1);
        }
        
        foreach ($order->orderHasProducts as $k => $val) {
            $objectExcel->setActiveSheetIndex(0)->mergeCells('C' . (19 + $k) . ':G' . (19 + $k));
            $objectExcel->setActiveSheetIndex(0)->mergeCells('H' . (19 + $k) . ':J' . (19 + $k));
            $objectExcel->setActiveSheetIndex(0)->mergeCells('Z' . (19 + $k) . ':AC' . (19 + $k));
            
            $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (19 + $k), $k + 1);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('C' . (19 + $k), $val->name);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('K' . (19 + $k), $val->productFeature->measurement);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('M' . (19 + $k), $val->productFeature->tare);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('O' . (19 + $k), $val->quantity);
            $objectExcel->setActiveSheetIndex(0)->setCellValue('T' . (19 + $k), number_format(sprintf("%01.2f", $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + $k), number_format(sprintf("%01.2f", $val->quantity * $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + $k), number_format(sprintf("%01.2f", $val->quantity * $val->price), 2, '.', ' '));
            $objectExcel->setActiveSheetIndex(0)->setCellValue('Z' . (19 + $k), 'Без НДС');
            
            $total_summ += $val->total;
        }

        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (19 + count($order->orderHasProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (19 + count($order->orderHasProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('X' . (20 + count($order->orderHasProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (20 + count($order->orderHasProducts)), $total_summ);
        $objectExcel->setActiveSheetIndex(0)->setCellValue('F' . (36 + count($order->orderHasProducts) - 1), '"' . Yii::$app->formatter->asDate($parameters['currentDate'], 'php:d') . '"');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('I' . (36 + count($order->orderHasProducts) - 1), Yii::$app->formatter->asDate($parameters['currentDate'], 'php:Y') . ' года');
        $objectExcel->setActiveSheetIndex(0)->setCellValue('G' . (36 + count($order->orderHasProducts) - 1), Yii::$app->formatter->asDate($parameters['currentDate'], 'php:F'));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('B' . (29 + count($order->orderHasProducts) - 1), Sum::toStr($total_summ));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('E' . (23 + count($order->orderHasProducts) - 1), Sum::toStr(count($order->orderHasProducts), false));
        $objectExcel->setActiveSheetIndex(0)->setCellValue('AG' . (33 + count($order->orderHasProducts) - 1), $parameters['shortName']);
        
        $objectWriter = \PHPExcel_IOFactory::createWriter($objectExcel, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $attachmentName .'"');
        header('Cache-Control: max-age=0');

        $objectWriter->save('php://output');

        exit();
    }
    
    public function actionIndex()
    {
        $orders_date = Order::getPurchaseDates(0, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
        $dates = [];
        if ($orders_date) {
            foreach ($orders_date as $k => $date) {
                $dateInit = strtotime($date['purchase_date']);
                if ($k == 0) {
                    $dateStart = date('Y-m-d 21:00:00', $dateInit);
                    $dateEnd = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) + 1, date('Y', $dateInit)));
                    $o_count = Order::getOrdersCount($dateStart, $dateEnd, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
                    if ($o_count['cnt'] > 0) {
                        $dates[] = ['start' => $dateStart, 'end' => $dateEnd];
                    }
                }
                $dateEnd = date('Y-m-d 21:00:00', $dateInit);
                $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
                $o_count = Order::getOrdersCount($dateStart, $dateEnd, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
                if ($o_count['cnt'] > 0) {
                    $dates[] = ['start' => $dateStart, 'end' => $dateEnd];
                } else {
                    if ($k != 0) {
                        $nextDate = $orders_date[$k - 1]['purchase_date'];
                        $datesDiff = (strtotime($nextDate) - strtotime($date['purchase_date']))/3600/24;
                        if ($datesDiff > 1) {
                            $dateStart = date('Y-m-d 21:00:00', $dateInit);
                            $dateEnd = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) + 1, date('Y', $dateInit)));
                            $dates[] = ['start' => $dateStart, 'end' => $dateEnd];
                        }
                    }
                }
            }
        }
        
        return $this->render('index', [
            'dates' => $dates,
        ]);
    }
    
    public function actionDate($date)
    {
        $dateInit = strtotime($date);
        $dateEnd = date('Y-m-d 21:00:00', $dateInit);
        $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
        
        $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
        return $this->render('date', [
            'dataProvider' => $dataProvider,
            'date' => ['start' => $dateStart, 'end' => $dateEnd]
        ]);
    }
    
    public function actionGetDetalization()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'co',
            'dts' => date('Y-m-d', strtotime($_POST['date_s'])),
            'dte' => date('Y-m-d', strtotime($_POST['date_e'])) 
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->entity->id;
            $view_model->section = 'co';
            $view_model->dts = $_POST['date_s'];
            $view_model->dte = $_POST['date_e'];
        }
        
        $view_model->detail = 'opened';
        $view_model->save();
        
        $dateEnd = date('Y-m-d 21:00:00', strtotime($_POST['date_e']));
        $dateStart = date('Y-m-d 21:00:00', strtotime($_POST['date_s']));
        
        $dataProvider = Order::getDetalizationStock($dateStart, $dateEnd, 0);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date_e' => $dateEnd,
            'date_s' => $dateStart,
        ]);
    }
    
    public function actionShowAll()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'co',
            'dts' => date('Y-m-d', strtotime($_POST['date_s'])),
            'dte' => date('Y-m-d', strtotime($_POST['date_e'])) 
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->entity->id;
            $view_model->section = 'co';
            $view_model->dts = $_POST['date_s'];
            $view_model->dte = $_POST['date_e'];
        }
        
        $view_model->detail = 'closed';
        $view_model->save();
        
        $dateEnd = date('Y-m-d 21:00:00', strtotime($_POST['date_e']));
        $dateStart = date('Y-m-d 21:00:00', strtotime($_POST['date_s']));
        $dataProvider = Order::getDetalizationStock($dateStart, $dateEnd, 0, 1);
        $models = $dataProvider->getModels();
        foreach ($models as $model) {
            $model->hide = 0;
            $model->save();
        }
        return true;
    }
    
    public function actionHide()
    {
        $order_id = $_POST['o_id'];
        $dateStart = $_POST['date_s'];
        $dateEnd = $_POST['date_e'];
        
        $order = Order::findOne($order_id);
        $order->hide = 1;
        $order->save();
        
        $dataProvider = Order::getDetalizationStock($dateStart, $dateEnd, 0);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date_e' => $dateEnd,
            'date_s' => $dateStart,
        ]);
    }
    
    public function actionSetView()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'co',
            'dts' => date('Y-m-d', strtotime($_POST['date_s'])),
            'dte' => date('Y-m-d', strtotime($_POST['date_e'])) 
        ])->one();
        
        if ($view_model) {
            if ($view_model->detail == 'opened') {
                $dateEnd = date('Y-m-d 21:00:00', strtotime($_POST['date_e']));
                $dateStart = date('Y-m-d 21:00:00', strtotime($_POST['date_s']));
                
                $dataProvider = Order::getDetalizationStock($dateStart, $dateEnd, 0);
                return $this->renderPartial('_detail', [
                    'dataProvider' => $dataProvider,
                    'date_e' => $dateEnd,
                    'date_s' => $dateStart,
                ]);
            }
        }
        
        return false;
    }
    
    public function actionDetail($id, $pid, $prid, $date)
    {
        $dateInit = strtotime($date);
        $dateEnd = date('Y-m-d 21:00:00', $dateInit);
        $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
        $partner = Partner::findOne($pid);
        //$product = Product::findOne($id);
        $provider = Provider::findOne($prid);
        $details = Order::getProviderOrderDetailsStock($id, ['start' => $dateStart, 'end' => $dateEnd], $pid);
        return $this->render('detail', [
            'partner' => $partner,
            //'product' => $product,
            'provider' => $provider,
            'date' => $date,
            'date_s' => $dateStart,
            'details' => $details,
        ]);
    }
    
    public function actionAdminDelete($date)
    {
        $dateInit = strtotime($date);
        $dateEnd = date('Y-m-d 21:00:00', $dateInit);
        $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
        $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0);
        $models = $dataProvider->getModels();
        while (count($models)) {
            foreach ($models as $model) {
                $ohp = OrderHasProduct::findOne($model['ohp_id']);
                $ohp->deleted = 1;
                $ohp->save();
            }
            $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0);
            $models = $dataProvider->getModels();
        }
        
        $this->redirect(['index']);
    }
    
    public function actionDeleteStock($date = "")
    {
        if (empty($date)) {
            $date = $_POST['date'];
        }
        $dateInit = strtotime($date);
        $dateEnd = date('Y-m-d 21:00:00', $dateInit);
        $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
        $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0, -1);
        $models = $dataProvider->getModels();
        while (count($models)) {
            foreach ($models as $model) {
                $ohp = OrderHasProduct::findOne($model['ohp_id']);
                $ohp->delete();
            }
            $dataProvider = Order::getProvidersOrderStock($dateStart, $dateEnd, 0, -1);
            $models = $dataProvider->getModels();
        }
        
        if (isset($_POST['date'])) {
            return true;
        } else {
            $this->redirect(['index']);
        }
    }
    
    public function actionSetCorrected()
    {
        $quantity = $_POST['quantity'];
        $total = $_POST['total'];
        $ohp_id = $_POST['id'];
        $prev_quantity = $_POST['prev_quantity'];
        
        $model = OrderHasProduct::findOne($ohp_id);
        if ($model) {
            $user = User::findOne($model->order->user_id);
            $model->productFeature->quantity += $prev_quantity; 
            if ($model->productFeature->quantity < $quantity) {
                $quantity = $model->productFeature->quantity;
                $model->productFeature->quantity = 0;
            } else {
                $model->productFeature->quantity -= $quantity;
            }
            $model->productFeature->save();
            
            
            $prev_total = $model->total;
            $model->quantity = $quantity;
            $model->total = $quantity * $model->price;
            $model->save();
            
            $model->order->total = OrderHasProduct::getSumTotalByOrder($model->order_id);
            $model->order->paid_total = OrderHasProduct::getSumTotalByOrder($model->order_id);
            $model->order->save();
            
            $prev_paid_for_provider = $paid_for_provider = 0;
            $stock_provider = ProviderStock::getCurrentStock($model->product_feature_id, $model->provider_id);
            $provider_model = Provider::findOne(['id' => $model->provider_id]);
            $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);
            if ($stock_provider) {
                $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                $stock_provider->reaminder_rent += $prev_quantity;
                $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                $stock_provider->summ_on_deposit = $stock_provider->total_sum - $stock_provider->summ_reminder;
                if ($stock_provider->reaminder_rent >= $quantity) {
                    $stock_provider->reaminder_rent -= $quantity;
                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                    $paid_for_provider =  $quantity * $body->summ;
                    $stock_provider->summ_on_deposit += $paid_for_provider;
                    $stock_provider->save();
                } else {
                    $rest = $quantity - $stock_provider->reaminder_rent;
                    $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                    $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                    $stock_provider->reaminder_rent = 0;
                    $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                    $stock_provider->save();
                    
                    while ($rest > 0) {
                        $stock_provider = ProviderStock::getCurrentStock($product->id, $provider_id);
                        
                        if ($stock_provider->reaminder_rent >= $rest) {
                            $stock_provider->reaminder_rent -= $rest;
                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                            $paid_for_provider = $rest * $body->summ;
                            $stock_provider->summ_on_deposit += $paid_for_provider;
                            $stock_provider->save();
                            $rest = 0;
                        } else {
                            $rest -= $stock_provider->reaminder_rent;
                            $body = StockBody::findOne(['id' => $stock_provider->stock_body_id]);
                            $stock_provider->summ_on_deposit += $stock_provider->reaminder_rent * $body->summ;
                            $stock_provider->reaminder_rent = 0;
                            $stock_provider->summ_reminder = $stock_provider->reaminder_rent * $body->summ;
                            $stock_provider->save();
                        }
                    }
                }
                
                if ($body->deposit == '1') {
                    $prev_paid_for_provider = $prev_quantity * $body->summ;
                    $paid_for_provider = $quantity * $body->summ;
                    if (!Account::swap($user->deposit, $provider_account, -$prev_paid_for_provider, 'Корректировка стоимости по заявке №' . $model->order_id, false)) {
                        throw new Exception('Ошибка модификации счета пользователя 1!');
                    }
                    if (!Account::swap($user->deposit, $provider_account, $paid_for_provider, 'Корректировка стоимости по заявке №' . $model->order_id, false)) {
                        throw new Exception('Ошибка модификации счета пользователя 2!');
                    }
                }
            }
            if (!Account::swap($user->deposit, null, -($prev_total - $prev_paid_for_provider), 'Корректировка стоимости')) {
               throw new Exception('Ошибка модификации счета пользователя 3!');
            }
            if (!Account::swap($user->deposit, null, $model->total - $paid_for_provider, 'Корректировка стоимости')) {
               throw new Exception('Ошибка модификации счета пользователя 4!');
            }
            
            Fund::setDeductionForOrder($model->product_feature_id, -$model->purchase_price, $prev_quantity);
            Fund::setDeductionForOrder($model->product_feature_id, $model->purchase_price, $quantity);
            
            return true;
        }
    }
}
