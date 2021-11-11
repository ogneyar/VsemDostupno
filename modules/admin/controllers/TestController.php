<?php

namespace app\modules\admin\controllers;

use Yii;
use app\models\User;
use app\models\Account;


class TestController extends BaseController
{
   
    /**
     * Tests.
     * @return mixed
     */
    public function actionIndex()

    {       
        $users = User::find()->where(['role' => [User::ROLE_MEMBER,User::ROLE_PARTNER,User::ROLE_PROVIDER]])->all();
        $accounts = [];
        foreach($users as $user)
        {
            // $user->id;
            $accounts[] = Account::find()->where(['user_id' => $user->id,'type' => 'deposit'])->all();
        }

        return $this->render('index', [
            'users' => $users,
            'accounts' => $accounts
            ]);
    }

}