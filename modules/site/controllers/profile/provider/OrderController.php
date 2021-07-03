<?php

namespace app\modules\site\controllers\profile\provider;

use Yii;
use app\modules\site\controllers\BaseController;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use app\models\User;
use app\models\Order;
use app\models\Partner;
use app\models\Product;
use app\models\Provider;
use app\models\OView;
use yii\web\ForbiddenHttpException;

use app\modules\purchase\models\PurchaseOrder;
use app\modules\purchase\models\PurchaseOrderProduct;


class OrderController extends BaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index',
                            'detail',
                            'hide',
                            'date',
                            'get-detalization',
                            'set-view',
                            'show-all',
                            'delete'
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
                            }

                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_PARTNER])) {
                                throw new ForbiddenHttpException('Действие не разрешено.');
                            }

                            if (Yii::$app->user->identity->entity->disabled) {
                                $action->controller->redirect('/profile/logout')->send();
                                exit();
                            }

                            return true;
                        },
                    ],
                ],
            ],
        ]);
    }
    
    public function actionIndex()
    {
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $purchases_date = PurchaseOrder::getPurchaseDatesByPartner($partner->id);
        //$dataProvider = Order::getProviderOrderByPartner($partner->id, $date, 1);
        
        return $this->render('index', [
            'purchases_date' => $purchases_date
        ]);
    }
    
    public function actionDetail($id, $pid, $prid, $date)
    {
        $partner = Partner::findOne($pid);
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
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $order_id = $_POST['o_id'];
        $date = $_POST['date'];
        
        $order = PurchaseOrder::findOne($order_id);
        $order->hide = 1;
        $order->save();
        
        $dataProvider = PurchaseOrder::getDetalizationByPartner($partner->id, $date);
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date' => $date,
        ]);
    }
    
    public function actionDate($date)
    {
        
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $dataProvider = PurchaseOrder::getProvidersOrderByPartner($partner->id, $date);
        
        return $this->render('date', [
            'date' => $date,
            'dataProvider' => $dataProvider,
            'partner_id' => $partner->id,
        ]);
    }
    
    public function actionGetDetalization()
    {
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->id;
            $view_model->section = 'po';
            $view_model->dts = $_POST['date'];
        }
        
        $view_model->detail = 'opened';
        $view_model->save();
        
        $dataProvider = PurchaseOrder::getDetalizationByPartner($partner->id, $_POST['date']); 
        return $this->renderPartial('_detail', [
            'dataProvider' => $dataProvider,
            'date' => $_POST['date'],
        ]);
    }
    
    public function actionSetView()
    {
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if ($view_model) {
            if ($view_model->detail == 'opened') {
                $dataProvider = PurchaseOrder::getDetalizationByPartner($partner->id, $_POST['date']);
                return $this->renderPartial('_detail', [
                    'dataProvider' => $dataProvider,
                    'date' => $_POST['date'],
                ]);
            }
        }
        
        return false;
    }
    
    public function actionShowAll()
    {
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $view_model = OView::find()->where([
            'user_id' => Yii::$app->user->identity->id,
            'section' => 'po',
            'dts' => date('Y-m-d', strtotime($_POST['date'])),
        ])->one();
        
        if (!$view_model) {
            $view_model = new OView;
            $view_model->user_id = Yii::$app->user->identity->id;
            $view_model->section = 'po';
            $view_model->dts = $_POST['date'];
        }
        
        $view_model->detail = 'closed';
        $view_model->save();
        
        $dataProvider = PurchaseOrder::getDetalizationByPartner($partner->id, $_POST['date'], 1);
        $models = $dataProvider->getModels();
        foreach ($models as $model) {
            $model->hide = 0;
            $model->save();
        }
        return true;
    }
    
    public function actionDelete($date)
    {
        $partner = Partner::getByUserId(Yii::$app->user->identity->id);
        $dataProvider = PurchaseOrder::getProvidersOrderByPartner($partner->id, $date);
        $models = $dataProvider->getModels();
        while (count($models)) {
            foreach ($models as $model) {
                $ohp = PurchaseOrderProduct::findOne($model['ohp_id']);
                $ohp->deleted_p = 1;
                $ohp->save();
            }
            $dataProvider = PurchaseOrder::getProvidersOrderByPartner($partner->id, $date);
            $models = $dataProvider->getModels();
        }
        
        $this->redirect(['index']);
    }
}