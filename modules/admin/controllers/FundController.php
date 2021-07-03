<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Fund;
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
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionAdd()
    {
        $model = new Fund();
        $model->name = $_POST{'name'};
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