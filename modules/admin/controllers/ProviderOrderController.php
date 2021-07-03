<?php
namespace app\modules\admin\controllers;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\Order;
use app\models\OrderHasProduct;
use app\models\Partner;
use app\models\Product;
use app\models\Provider;
use app\models\User;
use app\models\OView;
use app\models\Fund;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;
use app\modules\purchase\models\PurchaseFundBalance;

class ProviderOrderController extends BaseController
{
    public function actionIndex()
    {   
        //$purchases_date = Order::getPurchaseDates(1, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
        $purchases_date = PurchaseOrder::getPurchaseDates(Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
        //print_r($purchases_date);
        /*$dataProviderAll = $dates = [];
        $orders_date = Order::getOrdersDate();
        if ($orders_date) {
            foreach ($orders_date as $k => $date) {
                $dateInit = strtotime($date['order_date']);
                $dateEnd = date('Y-m-d 21:00:00', $dateInit);
                $dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) - 1, date('Y', $dateInit)));
                $dataProvider = Order::getProvidersOrder($dateStart, $dateEnd, 1, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
                $dataProviderAll[] = $dataProvider;
                $dates[] = ['start' => $dateStart, 'end' => $dateEnd];
                
                if ($k != 0) {
                    $nextDate = $orders_date[$k - 1]['order_date'];
                    $datesDiff = (strtotime($nextDate) - strtotime($date['order_date']))/3600/24;
                    if ($datesDiff > 1) {
                        $dateStart = date('Y-m-d 21:00:00', $dateInit);
                        $dateEnd = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m', $dateInit), date('d', $dateInit) + 1, date('Y', $dateInit)));
                        $dataProvider = Order::getProvidersOrder($dateStart, $dateEnd, 1, Yii::$app->user->identity->entity->role == User::ROLE_SUPERADMIN ? -1 : 0);
                        $dataProviderAll[] = $dataProvider;
                        $dates[] = ['start' => $dateStart, 'end' => $dateEnd];
                    }
                }
            }
        }*/
        //$dateEnd = date('Y-m-d 21:00:00');
        //$dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m'), date('d') - 1, date('Y')));
        
        return $this->render('index',[
            //'dataProvider' => Order::getProvidersOrder($dateStart, $dateEnd),
            //'date' => ['start' => $dateStart, 'end' => $dateEnd],
            //'dataProviderAll' => $dataProviderAll,
            //'dates' => $dates,
            'purchases_date' => $purchases_date
        ]);
    }
    
    public function actionDetail($id, $pid, $prid, $date)
    {
        //$dateEnd = date('Y-m-d 21:00:00');
        //$dateStart = date('Y-m-d H:i:s', mktime(21, 0, 0, date('m'), date('d') - 1, date('Y')));
        //$dateEnd = "2017-10-08 21:00:00";
        //$dateStart = "2017-10-07 21:00:00";
        
        $partner = Partner::findOne($pid);
        //$product = Product::findOne($id);
        $provider = Provider::findOne($prid);
        $details = PurchaseOrder::getProviderOrderDetails($id, $date, $pid);
        return $this->render('detail', [
            'partner' => $partner,
            //'product' => $product,
            'provider' => $provider,
            'date' => $date,
            'details' => $details,
        ]);
    }
    
    public function actionHide()
    {
        $order_id = $_POST['o_id'];
        $date = $_POST['date'];
        
        $order = PurchaseOrder::findOne($order_id);
        $order->hide = 1;
        $order->save();
        
        $dataProvider = PurchaseOrder::getDetalization($date);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date' => $date,
        ]);
    }
    
    public function actionDate($date)
    {
        $dataProvider = PurchaseOrder::getProvidersOrder($date);
        
        return $this->render('date', [
            'date' => $date,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionGetDetalization()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->entity->id;
            $view_model->section = 'po';
            $view_model->dts = $_POST['date'];
        }
        
        $view_model->detail = 'opened';
        $view_model->save();
        
        $dataProvider = PurchaseOrder::getDetalization($_POST['date']);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date' => $_POST['date'],
        ]);
    }
    
    public function actionShowAll()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->entity->id;
            $view_model->section = 'po';
            $view_model->dts = $_POST['date'];
        }
        
        $view_model->detail = 'closed';
        $view_model->save();
        
        $dataProvider = PurchaseOrder::getDetalization($_POST['date'], 1);
        $models = $dataProvider->getModels();
        foreach ($models as $model) {
            $model->hide = 0;
            $model->save();
        }
        return true;
    }
    
    public function actionAdminDelete($date)
    {
        $dataProvider = PurchaseOrder::getProvidersOrder($date);
        $models = $dataProvider->getModels();
        while (count($models)) {
            foreach ($models as $model) {
                $ohp = PurchaseOrderProduct::findOne($model['ohp_id']);
                $ohp->deleted = 1;
                $ohp->save();
            }
            $dataProvider = PurchaseOrder::getProvidersOrder($date);
            $models = $dataProvider->getModels();
        }
        
        $this->redirect(['index']);
    }
    
    public function actionDelete($date = "")
    {
        if (empty($date)) {
            $date = $_POST['date'];
        }
        
        $dataProvider = PurchaseOrder::getProvidersOrder($date, -1);
        $models = $dataProvider->getModels();
        while (count($models)) {
            foreach ($models as $model) {
                $ohp = PurchaseOrderProduct::findOne($model['ohp_id']);
                $ohp->delete();
            }
            $dataProvider = PurchaseOrder::getProvidersOrder($date, -1);
            $models = $dataProvider->getModels();
        }
        
        if (isset($_POST['date'])) {
            return true;
        } else {
            $this->redirect(['index']);
        }
    }
    
    public function actionSetView()
    {
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->entity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if ($view_model) {
            if ($view_model->detail == 'opened') {
                $dataProvider = PurchaseOrder::getDetalization($_POST['date']);
                return $this->renderPartial('_detail', [
                    'dataProvider' => $dataProvider,
                    'date' => $_POST['date'],
                ]);
            }
        }
        
        return false;
    }
    
    public function actionPayFund()
    {
        $order_id = $_POST['o_id'];
        $date = $_POST['date'];
        
        $order = PurchaseOrder::findOne($order_id);
        foreach ($order->purchaseOrderProducts as $product) {
            $purchase_fund = PurchaseFundBalance::find()->where(['purchase_order_product_id' => $product->id])->one();
            $purchase_fund->paid = 1;
            $purchase_fund->save();
            $fund_common = Fund::findOne($purchase_fund->fund_id);
            $fund_common->deduction_total += $purchase_fund->total;
            $fund_common->save();
        }
        
        $dataProvider = PurchaseOrder::getDetalization($date);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date' => $date,
        ]);
    }
}
