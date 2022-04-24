<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Account;
use app\models\Fund;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class FundController extends BaseController
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
    
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Fund::find(),
        ]);

        $balance = 0;
        $accounts = Account::find()->where(['type' => [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_STORAGE]])->all();
        foreach($accounts as $acc) {
            $balance += $acc->total;
        }

        $minus = 0;
        $accounts = Account::find()->where(['type' => Account::TYPE_SUBSCRIPTION])->all();
        foreach($accounts as $acc) {
            if ($acc->total > 0) $minus += $acc->total;
        }

        $po = 0;
        $friend = 0;
        $subscrib = 0;
        $storage = 0;
        $user = User::find()->where(['role' => User::ROLE_SUPERADMIN,'disabled' => '0'])->all();
        if ($user) {
            $user_id = $user[0]->id;
            // $account = Account::find()->where(['user_id' => $user_id,'type' => Account::TYPE_DEPOSIT])->all();
            $account = Account::find()->where(['user_id' => $user_id])->all();
            if ($account) {
                foreach($account as $acc) {
                    if ($acc->type == Account::TYPE_DEPOSIT) $po = $acc->total; // счёт ПО
                    if ($acc->type == Account::TYPE_BONUS) $friend = $acc->total; // счёт содружества
                    if ($acc->type == Account::TYPE_SUBSCRIPTION) $subscrib = $acc->total; // сумма взымаемых членских взносов
                    if ($acc->type == Account::TYPE_STORAGE) $storage = $acc->total; // Членские взносы
                }
            }
        }
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'balance' => $balance,
            'po' => $po,
            'friend' => $friend,
            'minus' => $minus,
            'subscrib' => $subscrib,
            'storage' => $storage,
        ]);
    }
    
    public function actionDistribute()
    {
         return $this->render('distribute', []);
    }

    public function actionAdd()
    {
        $model = new Fund();
        $model->name = $_POST['name'];
        $model->percent = $_POST['percent'];
        if ($model->save()) {
            return true;
        }
        return false;
    }
    
    public function actionUpdate()
    {
        $model = $this->findModel($_POST['id']);
        $model->name = $_POST['name'];
        $model->percent = $_POST['percent'];
        if ($model->save()) {
            return true;
        }
        return false;
    }
    
    public function actionGetFund()
    {
        $model = $this->findModel($_POST['id']);
        $res = [
            'name' => $model->name,
            'percent' => $model->percent
        ];
        return json_encode($res);
    }
    
    public function actionDelete()
    {
        $model = $this->findModel($_POST['id']);
        if ($model->delete()) {
            return true;
        }
        return false;
    }
    
    protected function findModel($id)
    {
        if (($model = Fund::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionTransfer()
    {
        $fund_from_id = $_POST['from_id'];
        $fund_to_id = isset($_POST['to_id']) ? $_POST['to_id'] : 0;
        $amount = $_POST['amount'];
        
        $fund_from = Fund::find()->where(['id' => $fund_from_id])->one();
        if ($amount <= $fund_from->deduction_total) {
            $fund_from->deduction_total -= $amount;
            $fund_from->save();
            if ($fund_to_id != 0) {
                $fund_to = Fund::find()->where(['id' => $fund_to_id])->one();
                $fund_to->deduction_total += $amount;
                $fund_to->save();
            }
        }
    }

    
    
}