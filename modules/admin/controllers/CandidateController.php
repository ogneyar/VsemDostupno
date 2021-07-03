<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\Candidate;
use app\models\CandidateGroup;
use yii\data\ActiveDataProvider;
use app\modules\admin\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;
use yii\web\Response;

/**
 * CandidateController implements the CRUD actions for Candidate model.
 */
class CandidateController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Candidate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $groups = CandidateGroup::find()->all();
        
        $modelGroup = new CandidateGroup;
        $modelCandidate = new Candidate;

        return $this->render('index', [
            'groups' => $groups,
            'modelGroup' => $modelGroup,
            'modelCandidate' => $modelCandidate,
        ]);
    }

    /**
     * Displays a single Candidate model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Candidate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Candidate();
        $groups = CandidateGroup::find()->all();
        
        $last_candidate = Candidate::getLastInserted();
        
        $request = Yii::$app->getRequest();
        if ($request->isAjax && $model->load($request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->renderAjax('_form-modal', [
            'modelCandidate' => $model,
            'groups' => $groups,
            'last_candidate' => $last_candidate,
        ]);
    }

    /**
     * Updates an existing Candidate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $groups = CandidateGroup::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'groups' => $groups,
            ]);
        }
    }

    /**
     * Deletes an existing Candidate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Candidate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Candidate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Candidate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionUpdateBlocking()
    {
        $post = Yii::$app->request->post();
        $model = Candidate::findOne($post['id']);
        
        $model->block_mailing = $post['block'];
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'success' => $model->save(),
        ];
    }
}
