<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpWord\TemplateProcessor;
use app\models\Account;
use app\models\Email;
use app\models\Forgot;
use app\models\Member;
use app\models\Template;
use app\models\User;
use app\modules\admin\models\MemberForm;
use yii\db\Query;
use app\models\Provider;
use app\models\Candidate;


/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends BaseController
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
     * Lists all Member models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            // 'query' => Member::find()->joinWith('user')->where('user.request = 0'),
            'query' => Member::find()->joinWith('user')->where('user.request = 0')->andWhere("user.role = 'member'"),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Member model.
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
     * Creates a new Member model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MemberForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user = new User();
                $user->scenario = 'admin_creation';
                $user->role = User::ROLE_MEMBER;
                $user->disabled = $model->disabled;
                $user->email = $model->email;
                $user->phone = $model->phone;
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
                $user->recommender_info = $model->recommender_info ? $model->recommender_info : null;
                $user->number = $model->number ? $model->number : (int) User::find()->max('number') + 1;
                $user->recommender_id = $model->recommender_id ? $model->recommender_id : 95;

                if (!$user->save()) {
                    throw new Exception('Ошибка создания пользователя!');
                }

                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION];
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    if (!$account->save()) {
                        throw new Exception('Ошибка создания счета пользователя!');
                    }
                }

                $member = new Member();
                $member->user_id = $user->id;
                $member->partner_id = $model->partner;
                $member->become_provider = $model->become_provider;
                if (!$member->save()) {
                    throw new Exception('Ошибка создания участника!');
                }
                $model->id = $member->id;
                
                if ($member->become_provider == 1) {
                    $provider = new Provider;
                    $provider->user_id = $member->user_id;
                    $provider->name = $member->user->shortName;
                    $provider->save();
                    $member->user->role = User::ROLE_PROVIDER;
                    $member->user->save();
                }
                
                $forgot = new Forgot();
                $forgot->user_id = $user->id;
                if (!$forgot->save()) {
                    throw new Exception('Ошибка создания уведомления для участника!');
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
                Email::send('register-candidate', Yii::$app->params['superadminEmail'], [
                    'link' => $candidate
                ]);
            }
            
            Email::send('forgot', $user->email, ['url' => $forgot->url]);

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Member model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $member = $this->findModel($id);
        $model = new MemberForm([
            'isNewRecord' => false,
            'id' => $id,
            'user_id' => $member->user->id,
            'disabled' => $member->user->disabled,
            'partner' => $member->partner_id,
            'email' => $member->user->email,
            'phone' => $member->user->phone,
            'ext_phones' => $member->user->ext_phones,
            'firstname' => $member->user->firstname,
            'lastname' => $member->user->lastname,
            'patronymic' => $member->user->patronymic,
            'birthdate' => mb_substr($member->user->birthdate, 0, 10, Yii::$app->charset),
            'citizen' => $member->user->citizen,
            'registration' => $member->user->registration,
            'residence' => $member->user->residence,
            'passport' => $member->user->passport,
            'passport_date' => strtotime($member->user->passport_date) > 0 ? date('Y-m-d', strtotime($member->user->passport_date)) : '',
            'passport_department' => $member->user->passport_department,
            'itn' => $member->user->itn,
            'skills' => $member->user->skills,
            'recommender_info' => $member->user->recommender_info,
            'number' => $member->user->number,
            'recommender_id' => $member->user->recommender_id,
            'become_provider' => $member->become_provider,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $member = Member::findOne($id);
            $member->user->scenario = 'admin_creation';
            $member->user->disabled = $model->disabled;
            $member->user->phone = $model->phone;
            $member->user->ext_phones = $model->ext_phones;
            $member->user->firstname = $model->firstname;
            $member->user->lastname = $model->lastname;
            $member->user->patronymic = $model->patronymic;
            $member->user->birthdate = $model->birthdate;
            $member->user->citizen = $model->citizen;
            $member->user->registration = $model->registration;
            $member->user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
            $member->user->passport = preg_replace('/\D+/', '', $model->passport);
            $member->user->passport_date = $model->passport_date;
            $member->user->passport_department = $model->passport_department;
            $model->itn = preg_replace('/\D+/', '', $model->itn);
            $member->user->itn = $model->itn ? $model->itn : null;
            $member->user->skills = $model->skills ? $model->skills : null;
            $member->user->recommender_info = $model->recommender_info ? $model->recommender_info : null;
            $member->user->number = $model->number ? $model->number : null;
            $member->user->recommender_id = $model->recommender_id ? $model->recommender_id : null;
            $member->user->save();

            $member->partner_id = $model->partner;
            $member->become_provider = $model->become_provider;


            if ($member->become_provider == 1) {
                $provider = new Provider;
                $provider->scenario = 'become_provider';
                $provider->user_id = $member->user_id;
                $provider->name = $member->user->shortName;
                $provider->save();
                $member->user->role = User::ROLE_PROVIDER;
                $member->user->save();
            } else {
                $provider = Provider::find()->where('user_id = :user_id', [':user_id' => $member->user_id])->one();
                if ($provider) {
                    $provider->delete();
                    $member->user->role = User::ROLE_MEMBER;
                    $member->user->save();
                }
            }

            $member->save();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Member model.
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
     * Finds the Member model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Member the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Member::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionDownloadProtocol($startDate, $endDate)
    {
        $members = Member::find()
            ->joinWith('user')
            ->where('created_at >= :startDate AND created_at <= :endDate', [
                ':startDate' => $startDate,
                ':endDate' => date('Y-m-d', strtotime($endDate) + 86400)
            ])
            ->all();

        if (!$members) {
            throw new NotFoundHttpException('Участники не найдены.');
        }

        $memberShortNames = [];
        foreach ($members as $index => $member) {
            $memberShortNames[] = $member->shortName;
        }

        $parameters = [
            'memberShortNames' => implode(', ', $memberShortNames),
            'currentDate' => date('d.m.Y'),
            'protocolNumber' => date('dm-y'),
        ];

        $templateName = preg_replace('/^download-/', '', $this->action->id);
        $templateFile = Template::getFileByName('member', $templateName);
        if (!$templateFile) {
            throw new NotFoundHttpException('Шаблон не найден.');
        }
        $templateExtension = pathinfo($templateFile, PATHINFO_EXTENSION);
        $attachmentName = sprintf('%s-%s.%s', $templateName, $parameters['protocolNumber'], $templateExtension);

        $templateProcessor = new TemplateProcessor($templateFile);

        foreach ($parameters as $name => $value) {
            $templateProcessor->setValue($name, $value);
        }

        return Yii::$app->response->sendFile(
            $templateProcessor->save(),
            $attachmentName
        );
    }
     
}
