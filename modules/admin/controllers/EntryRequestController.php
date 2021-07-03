<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\models\User;
use app\models\Member;
use app\models\Provider;
use app\models\Email;
use app\modules\admin\models\MemberForm;
use app\modules\admin\models\ProviderForm;

class EntryRequestController extends BaseController
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

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->where('request = 1'),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionView($id)
    {
        $user = User::findOne($id);
        if (isset($user->member)) {
            $model = $user->member;
        } else if (isset($user->provider)) {
            $model = $user->provider;
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }
    
    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        if (isset($user->member)) {
            $member = Member::findOne($user->member->id);
            $model = new MemberForm([
                'isNewRecord' => false,
                'id' => $id,
                'user_id' => $member->user->id,
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
                'recommender_id' => $member->user->recommender_id,
                'become_provider' => $member->become_provider,
            ]);
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $member = Member::findOne($user->member->id);
                $member->user->scenario = 'admin_creation';
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
                $member->user->recommender_id = $model->recommender_id ? $model->recommender_id : null;
                $member->user->save();
                $member->partner_id = $model->partner;
                
                $member->save();
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else if (isset($user->provider)) {
            $provider = Provider::findOne($user->provider->id);
            $model = new ProviderForm([
                'isNewRecord' => false,
                'id' => $id,
                'user_id' => $provider->user->id,
                'name' => $provider->name,
                'disabled' => $provider->user->disabled,
                'email' => $provider->user->email,
                'phone' => $provider->user->phone,
                'ext_phones' => $provider->user->ext_phones,
                'firstname' => $provider->user->firstname,
                'lastname' => $provider->user->lastname,
                'patronymic' => $provider->user->patronymic,
                'birthdate' => mb_substr($provider->user->birthdate, 0, 10, Yii::$app->charset),
                'citizen' => $provider->user->citizen,
                'registration' => $provider->user->registration,
                'residence' => $provider->user->residence,
                'passport' => $provider->user->passport,
                'passport_date' => strtotime($provider->user->passport_date) > 0 ? date('Y-m-d', strtotime($provider->user->passport_date)) : '',
                'passport_department' => $provider->user->passport_department,
                'itn' => $provider->user->itn,
                'skills' => $provider->user->skills,
                'number' => $provider->user->number,
                'categoryIds' => $provider->categoryIds,
                'categories' => $provider->categories,
                'recommender_id' => $provider->user->recommender_id,
                'field_of_activity' => $provider->field_of_activity,
                'offered_goods' => $provider->offered_goods,
                'snils' => $provider->snils,
                'legal_address' => $provider->legal_address,
                'ogrn' => $provider->ogrn,
                'site' => $provider->site,
                'description' => $provider->description,
            ]);
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $provider = Provider::findOne($user->provider->id);
                $provider->user->disabled = $model->disabled;
                $provider->user->phone = $model->phone;
                $provider->user->ext_phones = $model->ext_phones;
                $provider->user->firstname = $model->firstname;
                $provider->user->lastname = $model->lastname;
                $provider->user->patronymic = $model->patronymic;
                $provider->user->birthdate = $model->birthdate;
                $provider->user->citizen = $model->citizen;
                $provider->user->registration = $model->registration;
                $provider->user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
                $provider->user->passport = preg_replace('/\D+/', '', $model->passport);
                $provider->user->passport_date = $model->passport_date;
                $provider->user->passport_department = $model->passport_department;
                $model->itn = preg_replace('/\D+/', '', $model->itn);
                $provider->user->itn = $model->itn ? $model->itn : null;
                $provider->user->skills = $model->skills ? $model->skills : null;
                $provider->user->number = $model->number ? $model->number : null;
                $provider->user->recommender_id = $model->recommender_id ? $model->recommender_id : null;
                $provider->user->save();

                $provider->name = $model->name;
                $provider->categoryIds = $model->categoryIds;
                $provider->field_of_activity = $model->field_of_activity;
                $provider->offered_goods = $model->offered_goods;
                $provider->snils = $model->snils;
                $provider->legal_address = $model->legal_address;
                $provider->ogrn = $model->ogrn;
                $provider->site = $model->site;
                $provider->description = $model->description;
                $provider->save();

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }
    
    public function actionDelete($id)
    {
        $user = User::findOne($id);
        $user->delete();

        return $this->redirect(['index']);
    }
    
    public function actionAccept($id)
    {
        $user = User::findOne($id);
        $user->number = (int) User::find()->max('number') + 1;
        $user->request = 0;
        $user->disabled = 0;
        $user->scenario = 'admin_creation';
        $user->save();
        if (isset($user->provider)) {
            Email::send('active-profile-provider', $user->email, [
                'firstname' => $user->firstname,
                'patronymic' => $user->patronymic,
                'reg_number' => $user->number,
                'url' => Url::to(['/profile/login'], true),
            ]);
        } else if (isset($user->member)) {
            Email::send('active-profile', $user->email, [
                'firstname' => $user->firstname,
                'patronymic' => $user->patronymic,
                'reg_number' => $user->number,
                'url' => Url::to(['/profile/login'], true),
            ]);
        }
        return $this->redirect(['index']);
    }
}
