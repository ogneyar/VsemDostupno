<?php

namespace app\modules\admin\controllers;

use Yii;
use DateTime;
use app\models\Account;
use app\models\Archive;
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
        $funds = Fund::find()->all();
        foreach($funds as $fund) {
            $balance += $fund->deduction_total;
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

        
        $funds_select = [];
        foreach ($dataProvider->getModels() as $fund) {
            $funds_select[$fund->id] = $fund->name;
        }
        $reason_select = [];
        $reason_select[] = "Транспортные издержки";
        $reason_select[] = "Фасовка товаров";
        $reason_select[] = "Канцтовары, расходные материалы";
        $reason_select[] = "Закупка оборудования";
        $reason_select[] = "Коммунальные издержки";
        $reason_select[] = "Услуги связи";
        $reason_select[] = "Обмен паями";

        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'balance' => $balance,
            'po' => $po,
            'friend' => $friend,
            'minus' => $minus,
            'subscrib' => $subscrib,
            'storage' => $storage,
            'funds_select' => $funds_select,
            'reason_select' => $reason_select,
        ]);
    }
    
    public function actionDistribute()
    {
        return $this->render('distribute', []);
    }

    public function actionArchive()
    {
        $archive = Archive::find()->all();

        // второй вариант передачи данных
        $dataProvider = new ActiveDataProvider([
            'query' => Archive::find(),
        ]);

        return $this->render('archive', [
            'archive' => $archive,
            'dataProvider' => $dataProvider,
        ]);
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
        $fund_to_id = $_POST['to_id'];
        $amount = $_POST['amount'];
        $reason = $_POST['reason'];
        $user_id = $_POST['user_id'];
        
        $fund = null;
        if ($fund_from_id) {
            $fund = Fund::find()->where(['id' => $fund_from_id])->one();
            if ($amount <= $fund->deduction_total) {
                $fund->deduction_total -= $amount;
                $fund->save();
            }
        }else if ($fund_to_id) {
            $fund = Fund::find()->where(['id' => $fund_to_id])->one();
            $fund->deduction_total += $amount;
            $fund->save();
        }

        if ($fund) {
            // Запись данных в архив
            $archive = new Archive();
            $dTime = new DateTime();
            $archive->date = $dTime->format("Y-m-d H:i:s");
            $archive->operation = "Произведено списание средств";
            if ($fund_to_id) $archive->operation = "Произведено зачисление средств";
            $archive->account_name = $fund->name;
            $archive->amount = $amount;
            $archive->reason = $reason;
            $user = User::findOne($user_id);
            $archive->fio = $user->getShortName();
            $archive->number = $user->number;
            $archive->save();
        }

    }

    
    
}