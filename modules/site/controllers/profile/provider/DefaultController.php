<?php

namespace app\modules\site\controllers\profile\provider;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use app\modules\site\controllers\BaseController;
use app\modules\site\models\profile\provider\PersonalForm;
use app\models\User;
use app\models\Member;

class DefaultController extends BaseController
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
                            'personal',
                            'email',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
                            }

                            if (!in_array(Yii::$app->user->identity->role, [User::ROLE_PROVIDER])) {
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
        $member = Member::find()->where(['user_id' => $this->identity->entity->id])->one();
        if ($member) {
            $user_data = [
                'number' => $this->identity->entity->number,
                'firstname' => $this->identity->entity->firstname,
                'patronymic' => $this->identity->entity->patronymic,
            ];
            return $this->render('personal_m', [
                'user_data' => $user_data,
            ]);
        } else {
            $model = new PersonalForm([
                'user' => $this->identity->entity->id,
                'name' => $this->identity->entity->provider->name,
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
                'field_of_activity' => $this->identity->entity->provider->field_of_activity,
                'offered_goods' => $this->identity->entity->provider->offered_goods,
                'snils' => $this->identity->entity->provider->snils,
                'legal_address' => $this->identity->entity->provider->legal_address,
                'ogrn' => $this->identity->entity->provider->ogrn,
                'site' => $this->identity->entity->provider->site,
                'description' => $this->identity->entity->provider->description,
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

                $this->identity->entity->provider->name = $model->name;
                $this->identity->entity->provider->field_of_activity = $model->field_of_activity;
                $this->identity->entity->provider->offered_goods = $model->offered_goods;
                $this->identity->entity->provider->snils = $model->snils;
                $this->identity->entity->provider->legal_address = $model->legal_address;
                $this->identity->entity->provider->ogrn = $model->ogrn;
                $this->identity->entity->provider->site = $model->site;
                $this->identity->entity->provider->description = $model->description;
                $this->identity->entity->provider->save();
            }

            $model->password =
            $model->password_repeat = '';

            return $this->render('personal', [
                'model' => $model,
            ]);
        }
        
    }

    
    public function actionEmail()
    {
        $user_data = [
            'number' => $this->identity->entity->id,
            'firstname' => $this->identity->entity->firstname,
            'patronymic' => $this->identity->entity->patronymic,
        ];

        return $this->render('email', [
            'user_data' => $user_data,
        ]);
    }
    

}
