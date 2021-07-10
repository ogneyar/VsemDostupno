<?php

namespace app\modules\site\controllers\profile\member;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use app\modules\site\controllers\BaseController;
use app\modules\site\models\profile\member\PersonalForm;
use app\models\User;
use app\models\Order;
use app\models\Email;
use yii\helpers\Url;
use app\models\Member;
use app\models\NoticeEmail;

class DefaultController extends BaseController
{
    public function behaviors()
    {
        $enableOrder = false;
        if (in_array(Yii::$app->user->identity->role, [User::ROLE_MEMBER])) {
            $enableOrder = true;
        }
        if (Yii::$app->user->identity->role == User::ROLE_PROVIDER) {
            $member = Member::find()->where(['user_id' => Yii::$app->user->identity->id])->one();
            if ($member) {
                $enableOrder = true;
            }
        }
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'personal',
                            'email',
                            'order',
                            'becomeprovider',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) use ($enableOrder) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
                            }

                            if (!$enableOrder) {
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

    public function actionPersonal()
    {
        /*$model = new PersonalForm([
            'phone' => $this->identity->entity->phone,
            'ext_phones' => $this->identity->entity->ext_phones,
            'firstname' => $this->identity->entity->firstname,
            'lastname' => $this->identity->entity->lastname,
            'patronymic' => $this->identity->entity->patronymic,
            'birthdate' => strtotime($this->identity->entity->birthdate) > 0 ? date('d.m.Y', strtotime($this->identity->entity->birthdate)) : '',
            'citizen' => $this->identity->entity->citizen,
            'registration' => $this->identity->entity->registration,
            'residence' => $this->identity->entity->residence,
            'passport' => $this->identity->entity->passport,
            'passport_date' => strtotime($this->identity->entity->passport_date) > 0 ? date('d.m.Y', strtotime($this->identity->entity->passport_date)) : '',
            'passport_department' => $this->identity->entity->passport_department,
            'itn' => $this->identity->entity->itn,
            'skills' => $this->identity->entity->skills,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->password) {
                $this->identity->entity->password = $model->password;
            }
            $this->identity->entity->phone = '+' . preg_replace('/\D+/', '', $model->phone);
            $this->identity->entity->ext_phones = $model->ext_phones;
            $this->identity->entity->firstname = $model->firstname;
            $this->identity->entity->lastname = $model->lastname;
            $this->identity->entity->patronymic = $model->patronymic;
            $this->identity->entity->birthdate = date('Y-m-d', strtotime($model->birthdate));
            $this->identity->entity->citizen = $model->citizen;
            $this->identity->entity->registration = $model->registration;
            $this->identity->entity->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
            $this->identity->entity->passport = preg_replace('/\D+/', '', $model->passport);
            $this->identity->entity->passport_date = date('Y-m-d', strtotime($model->passport_date));
            $this->identity->entity->passport_department = $model->passport_department;
            $model->itn = preg_replace('/\D+/', '', $model->itn);
            $this->identity->entity->itn = $model->itn ? $model->itn : null;
            $this->identity->entity->skills = $model->skills ? $model->skills : null;
            $this->identity->entity->save();

            $this->identity->entity->member->save();
        }

        $model->password =
        $model->password_repeat = '';*/
        
        $user_data = [
            'number' => $this->identity->entity->number,
            'firstname' => $this->identity->entity->firstname,
            'patronymic' => $this->identity->entity->patronymic,
        ];

        return $this->render('personal', [
            //'model' => $model,
            'user_data' => $user_data,
        ]);
    }

    public function actionOrder()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->where('user_id = :user_id', [':user_id' => $this->identity->entity->id]),
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        return $this->render('order', [
            'title' => 'Мои заказы',
            'dataProvider' => $dataProvider,
        ]);
    }
    
   

    public function actionBecomeprovider()
    {
        $user = User::find()->where('id=:id',[':id'=>Yii::$app->user->identity->entity->id])->with('member')->one();
        
        if ($emails = NoticeEmail::getEmails()) {
            Email::send('notice-from-member', $emails, [
                'name' => $user->shortName,
                'phone' => $user->phone,
                'email' => $user->email,
                'viewUrl' => Url::to(['/admin/member/view', 'id' => $user->member->id], true),
            ]);
            
            Email::send('notice-from-member', $emails, [
                'name' => $user->shortName,
                'phone' => $user->phone,
                'email' => $user->email,
                'viewUrl' => Url::to(['/admin/member/view', 'id' => $user->member->id], true),
            ]);
        }
        
        Yii::$app->session->setFlash('Успех', 'Ваше уведомление отправлено, мы с Вами обязательно свяжемся в ближайшее время для обсуждения деталей сотрудничества');
        return $this->render('becomeprovider');
    }
}
