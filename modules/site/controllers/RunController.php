<?php

namespace app\modules\site\controllers;

use Yii;

use yii\web\Response;
use app\models\Account;
use app\models\SubscriberMessages;

use app\commands\SubscriberMonthPaymentController;


class RunController extends BaseController
{
   
    /**
     * 
     * @return mixed
     */
    public function actionIndex()
    {       
        return $this->render('index');
    }

    /**
     * 
     * @return mixed
     */
    public function actionPurchaseNotification()
    {       
        return $this->render('purchase-notification');
    }

    /**
     * 
     * @return array
     */
    public function actionUpdateSubscription()
    {       
        Yii::$app->response->format = Response::FORMAT_JSON;
        // return true;
        $value = "";
        if($_GET && ($_GET["value"] || $_GET["value"] == 0)) $value = $_GET["value"];
        
        if ($value || $value == 0) {
            $acc = Account::find()->where(['user_id' => Yii::$app->user->id,'type' => 'subscription'])->one();
            $acc->total = $value;
            $acc->save();
        }else {
            return  [
                'ok' => false,
                'message' => 'Значение не заменено.',
            ];
        }
        
        return  [
            'ok' => true,
            'message' => 'Значение заменено на ' . $value . '.',
            'value' => $value,
        ];
    }

    /**
     * оплата ежемесячных членских взносов
     * @return array
     */
    public function actionSubscriberPayment()
    {       
        // Yii::$app->response->format = Response::FORMAT_JSON;
        // return true;

        $controller = new SubscriberMonthPaymentController(Yii::$app->controller->id, Yii::$app);
        
        return $controller->actionIndex(); 

    }

    /**
     * удаление сообщений
     * @return redirect
     */
    public function actionDeleteRecordSubscriberMessages($id, $return)
    {       

        $subscriberMessages = SubscriberMessages::findOne($id);

        $subscriberMessages->delete();

        return $this->redirect($return); 

    }

}