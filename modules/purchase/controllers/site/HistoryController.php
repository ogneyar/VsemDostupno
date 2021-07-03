<?php

namespace app\modules\purchase\controllers\site;

use Yii;
use yii\web\Response;
use app\models\Provider;
use app\models\Account;
use app\models\User;
use app\models\ProviderStock;
use app\models\Email;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseProduct;
use app\modules\purchase\models\PurchaseProviderBalance;
use app\modules\purchase\models\PurchaseFundBalance;

class HistoryController extends BaseController
{
    public function actionIndex()
    {
        $purchases_date = PurchaseOrder::getPurchaseDatesByUser(Yii::$app->user->identity->id);
        
        return $this->render('index', [
            'purchases_date' => $purchases_date
        ]);
    }
    
    public function actionDetails($date)
    {
        $dataProvider = PurchaseOrder::getDetalizationByUser(Yii::$app->user->identity->id, $date);
        return $this->render('details', [
            'dataProvider' => $dataProvider,
            'date' => $date
        ]);
    }
    
    public function actionReorder($id, $date)
    {
        $order = PurchaseOrder::findOne($id);
        $new_order_total = $not_copy = 0;
        
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $new_order = new PurchaseOrder;
            $new_order->city_id = $order->city_id;
            $new_order->partner_id = $order->partner_id;
            $new_order->user_id = $order->user_id;
            $new_order->role = $order->role;
            $new_order->city_name = $order->city_name;
            $new_order->partner_name = $order->partner_name;
            $new_order->email = $order->email;
            $new_order->phone = $order->phone;
            $new_order->firstname = $order->firstname;
            $new_order->lastname = $order->lastname;
            $new_order->patronymic = $order->patronymic;
            $new_order->address = $order->address;
            $new_order->comment = $order->comment;
            $new_order->reorder = $order->id;
            $new_order->save();
            
            foreach ($order->purchaseOrderProducts as $product) {
                if ($product->purchaseProduct->purchase_date == $date) {
                    $copy = PurchaseProduct::find()->where(['copy' => $product->purchase_product_id])->one();
                    if ($copy) {
                        do {
                            if (strtotime($copy->stop_date) >= strtotime(date('Y-m-d'))) {
                                break;
                            }
                            $copy = PurchaseProduct::find()->where(['copy' => $copy->id])->one();;
                        } while ($copy);
                        
                        if ($copy) {
                            $new_product = new PurchaseOrderProduct;
                            $new_product->purchase_order_id = $new_order->id;
                            $new_product->product_id = $product->product_id;
                            $new_product->purchase_product_id = $copy->id;
                            $new_product->name = $product->name;
                            $new_product->price = $product->price;
                            $new_product->quantity = $product->quantity;
                            $new_product->total = $product->total;
                            $new_product->purchase_price = $product->purchase_price;
                            $new_product->provider_id = $product->provider_id;
                            $new_product->product_feature_id = $product->product_feature_id;
                            $new_product->status = 'advance';
                            $new_product->reorder = $product->id;
                            $new_product->save();
                            
                            $new_order_total += $new_product->total;
                        } else {
                            $not_copy ++;
                        }
                    } else {
                        $not_copy ++;
                    }
                }
            }
            if ($not_copy == count($order->purchaseOrderProducts)) {
                $new_order->delete();
            } else {
                $new_order->total = $new_order->paid_total = $new_order_total;
                $new_order->save();
                
                $order_has_product = PurchaseOrderProduct::find()->where(['purchase_order_id' => $new_order->id])->all();
                $total_paid_for_provider = 0;
                $deposit = Yii::$app->user->identity->entity->deposit;
                foreach ($order_has_product as $o_product) {
                    $provider_model = Provider::findOne(['id' => $o_product->provider_id]);
                    $provider_account = Account::findOne(['user_id' => $provider_model->user_id]);
                    
                    $provider_balance = new PurchaseProviderBalance;
                    $provider_balance->provider_id = $o_product->provider_id;
                    $provider_balance->user_id = Yii::$app->user->identity->id;
                    $provider_balance->purchase_order_product_id = $o_product->id;
                    $provider_balance->total = $o_product->quantity * $o_product->purchase_price;
                    $provider_balance->save();
                    
                    PurchaseFundBalance::setDeductionForOrder($o_product->id, Yii::$app->user->identity->id);
                    
                    $total_paid_for_provider += $provider_balance->total;
                    if (!Account::swap($deposit, $provider_account, $provider_balance->total, 'Перевод пая на счёт', false)) {
                        throw new Exception('Ошибка модификации счета пользователя!');
                    }
                }
                $message = 'Членский взнос';

                if (!Account::swap($deposit, null, $new_order->paid_total - $total_paid_for_provider, $message)) {
                   throw new Exception('Ошибка модификации счета пользователя!');
                }
                if (Yii::$app->user->identity->role == User::ROLE_PROVIDER) {
                    ProviderStock::setStockSum(Yii::$app->user->identity->id, $new_order->paid_total);
                }
                Email::send('add_advance_order', $new_order->email, [
                    'fio' => Yii::$app->user->identity->entity->respectedName,
                    'order_products' => $new_order->htmlEmailFormattedInformation,
                    'order_number' => $new_order->order_number . " (ПОВТОР)",
                ]);
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            Yii::$app->session->setFlash('cart-checkout', [
                'name' => 'cart-checkout-fail',
            ]);

            return $this->redirect('/cart/checkout');
        }
        
        return $this->redirect('index');
    }
    
    public function actionDelete($id, $date)
    {
        $delete_order = true;
        $order = PurchaseOrder::findOne($id);
        foreach ($order->purchaseOrderProducts as $product) {
            if ($product->purchaseProduct->purchase_date == $date) {
                $product->delete();
            } else {
                $delete_order = false;
            }
        }
        if ($delete_order) {
            $order->delete();
        }
        return $this->redirect('index');
    }
}