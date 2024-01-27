<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Account;
use app\models\AccountLog;
use app\models\Candidate;
use app\models\Email;
use app\models\Forgot;
use app\models\Partner;
use app\models\User;
use app\models\Member;
use app\modules\admin\models\AccountForm;
use app\modules\admin\models\PartnerForm;

/**
 * PartnerController implements the CRUD actions for Partner model.
 */
class PartnerController extends BaseController
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
     * Lists all Partner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = null;
        if (isset($_GET['number'])) {
            $query = Partner::find()->joinWith('user')->where('user.request = 0')->andWhere(['user.number' => $_GET['number']]);
        }else {
            $query = Partner::find()->joinWith('user')->where('user.request = 0');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Partner model.
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
     * Creates a new Partner model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PartnerForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user = new User();
                $user->role = User::ROLE_PARTNER;
                $user->disabled = $model->disabled;
                $user->tg_id = $model->tg_id;
                $user->email = $model->email;
                $user->phone = str_replace('+', '', $model->phone);
                $user->ext_phones = $model->ext_phones;
                $user->firstname = $model->firstname;
                $user->lastname = $model->lastname;
                $user->patronymic = $model->patronymic;
                $user->created_ip = Yii::$app->getRequest()->getUserIP();
                $user->birthdate = $model->birthdate;
                $user->citizen = $model->citizen;
                $user->registration = $model->registration;
                $user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
                $user->passport = preg_replace('/\D+/', '', $model->passport);
                $user->passport_date = $model->passport_date;
                $user->passport_department = $model->passport_department;
                $model->itn = preg_replace('/\D+/', '', $model->itn);
                $user->itn = $model->itn ? $model->itn : null;
                $user->skills = $model->skills ? $model->skills : null;
                // $user->number = $model->number ? $model->number : (int) User::find()->max('number') + 1;                
                $user->number = (int) User::find()->max('number') + 1;
                $user->recommender_id = $model->recommender_id ? $model->recommender_id : 95;
                $user->scenario = 'admin_creation';

                if (!$user->save()) {
                    throw new Exception('Ошибка создания пользователя!');
                }

                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION, Account::TYPE_GROUP, Account::TYPE_STORAGE, Account::TYPE_GROUP_FEE, Account::TYPE_FRATERNITY]; 
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    if (!$account->save()) {
                        throw new Exception('Ошибка создания счета пользователя!');
                    }
                }

                $partner = new Partner();
                $partner->user_id = $user->id;
                $partner->name = $model->name;
                $partner->city_id = $model->city;
                if (!$partner->save()) {
                    throw new Exception('Ошибка создания партнера!');
                }
                $model->id = $partner->id;

                $member = new Member();
                $member->user_id = $user->id;
                $member->partner_id = $partner->id;
                $member->become_provider = 0;
                if (!$member->save()) {
                    throw new Exception('Ошибка создания партнера как участника!');
                }

                $forgot = new Forgot();
                $forgot->user_id = $user->id;
                if (!$forgot->save()) {
                    throw new Exception('Ошибка создания уведомления для партнера!');
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new ForbiddenHttpException($e->getMessage());
            }
            
            $c_params = [
                'email' => $user->email,
            ];
            $candidate = Candidate::isCandidate($c_params);
            if ($candidate) {
                Email::tg_send('register-candidate', Yii::$app->params['superadminChatId'], [
                    'link' => $candidate
                ]);
            }
            
            // Email::tg_send('registr-small', Yii::$app->params['superadminChatId'], [
            //     'tg_id' => $user->tg_id,
            //     'name' => $user->getRespectedName(),
            //     'phone' => $user->phone,
            // ]);

            if ($user->tg_id) Email::tg_send('forgot', $user->tg_id, ['url' => $forgot->url]);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Partner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $partner = $this->findModel($id);
        $model = new PartnerForm([
            'isNewRecord' => false,
            'id' => $id,
            'user_id' => $partner->user->id,
            'tg_id' => $partner->user->tg_id,
            'city' => $partner->city_id,
            'name' => $partner->name,
            'disabled' => $partner->user->disabled,
            'email' => $partner->user->email,
            'phone' => $partner->user->phone,
            'ext_phones' => $partner->user->ext_phones,
            'firstname' => $partner->user->firstname,
            'lastname' => $partner->user->lastname,
            'patronymic' => $partner->user->patronymic,
            'birthdate' => mb_substr($partner->user->birthdate, 0, 10, Yii::$app->charset),
            'citizen' => $partner->user->citizen,
            'registration' => $partner->user->registration,
            'residence' => $partner->user->residence,
            'passport' => $partner->user->passport,
            'passport_date' => strtotime($partner->user->passport_date) > 0 ? date('Y-m-d', strtotime($partner->user->passport_date)) : '',
            'passport_department' => $partner->user->passport_department,
            'itn' => $partner->user->itn,
            'skills' => $partner->user->skills,
            'number' => $partner->user->number,
            'recommender_id' => $partner->user->recommender_id,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $partner = Partner::findOne($id);
            
            // $user = User::findOne($partner->user->id);
            // if (!$user) {
            //     throw new NotFoundHttpException('Участник не найден.');
            // }
            // $user->scenario = 'admin_creation';
            // $user->phone = 7;
            // echo("<script>console.log('user->phone', $user->phone)</script>");
            // echo("<script>console.log('user->role', $user->role)</script>");
            // if (!$user->save()) {
            //     throw new Exception('Ошибка редактирования пользователя!');
            // }
            
            $partner->user->scenario = 'admin_creation';
            $partner->user->disabled = $model->disabled;
            $partner->user->tg_id = $model->tg_id;
            $partner->user->phone = $model->phone;
            $partner->user->ext_phones = $model->ext_phones;
            $partner->user->firstname = $model->firstname;
            $partner->user->lastname = $model->lastname;
            $partner->user->patronymic = $model->patronymic;
            $partner->user->birthdate = $model->birthdate;
            $partner->user->citizen = $model->citizen;
            $partner->user->registration = $model->registration;
            $partner->user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
            $partner->user->passport = preg_replace('/\D+/', '', $model->passport);
            $partner->user->passport_date = $model->passport_date;
            $partner->user->passport_department = $model->passport_department;
            $model->itn = preg_replace('/\D+/', '', $model->itn);
            $partner->user->itn = $model->itn ? $model->itn : null;
            $partner->user->skills = $model->skills ? $model->skills : null;
            $partner->user->number = $model->number ? $model->number : null;
            $partner->user->recommender_id = $model->recommender_id ? $model->recommender_id : null;
            $partner->user->save();

            $partner->name = $model->name;
            $partner->city_id = $model->city;
            $partner->save();

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Partner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id)->user;
        $user->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Partner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Partner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Partner::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionAddress($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('address', [
            'model' => $model,
        ]);
    }
}
