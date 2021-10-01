<?php

namespace app\modules\site\controllers\profile;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use app\helpers\Html;
use yii\web\ForbiddenHttpException;
use app\modules\site\controllers\BaseController;
use app\models\Account;
use app\models\AccountLog;
use app\models\Email;
use app\models\Transfer;
use app\models\User;
use app\modules\site\models\account\TransferForm;

class AccountController extends BaseController
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
                            'index',
                            'swap',
                            'transfer',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect('/admin')->send();
                                exit();
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

    public function actionIndex()
    {
        $user = $this->identity->entity;

        $myAccounts = [];
        $accountTypes = ArrayHelper::getColumn($user->accounts, 'type');

        foreach ($accountTypes as $accountType) {
            $account = $user->getAccount($accountType);
            if ($account->type != Account::TYPE_GROUP 
                && $account->type != Account::TYPE_GROUP_FEE 
                && $account->type != Account::TYPE_FRATERNITY 
                && $account) {
                $myAccounts[] = [
                    'name' => Html::makeTitle($account->typeName),
                    'account' => $account,
                    'actionEnable' => $account->type == Account::TYPE_DEPOSIT,
                    'dataProvider' => new ActiveDataProvider([
                        'id' => $account->type,
                        'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account->id]),
                        'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                        'pagination' => [
                            'params' => array_merge($_GET, [
                                'type' => $account->type,
                            ]),
                        ],
                    ]),
                ];
            }
        }

        $groupAccounts = [];
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            $groupAccounts[] = [
                'name' => 'Расчётный счёт группы',
                'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_GROUP),
                'actionEnable' => false,
            ];

            $groupAccounts[] = [
                'name' => 'Членские взносы группы',
                'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_GROUP_FEE),
                'actionEnable' => false,
            ];

            // $sumAccounts = [
            //     Account::TYPE_GROUP_FEE => 'Членские взносы группы',
            // ];

            // foreach ($sumAccounts as $type => $name) {
            //     $groupAccounts[] = [
            //         'name' => $name,
            //         'account' => new Account([
            //             'total' => Account::find()
            //                 ->joinWith('member')
            //                 ->where('partner_id = :partner_id AND type = :type', [
            //                     ':partner_id' => Yii::$app->user->identity->entity->partner->id,
            //                     ':type' => $type,
            //                 ])
            //                 ->sum('total'),
            //         ]),
            //         'actionEnable' => false,
            //     ];
            // }
        }

        $fraternityAccount = [];
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            $fraternityAccount[] = [
                'name' => ' Счёт содружества',
                'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_FRATERNITY),
                'actionEnable' => false,
            ];
        }

        $accountType = Yii::$app->getRequest()->getQueryParam('type');
        if (!$user->getAccount($accountType)) {
            $accountType = Account::TYPE_DEPOSIT;
        }

        return $this->render('index', [
            'title' => 'Счета',
            'myAccounts' => $myAccounts,
            'groupAccounts' => $groupAccounts,
            'fraternityAccount' => $fraternityAccount,
            'accountType' => $accountType,
            'user' => $user,
        ]);
    }

    public function actionTransfer()
    {
        $get = Yii::$app->request->get();
        if (isset($get['token'])) {
            $transfer = Transfer::findOne(['token' => $get['token']]);

            if ($transfer && !$transfer->fromAccount->user->disabled) {
                $result = Account::swap(
                    $transfer->fromAccount,
                    $transfer->toAccount,
                    $transfer->amount,
                    $transfer->message
                );
                $transfer->delete();

                if ($result) {
                    Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-finish');
                    return $this->redirect('/profile/message');
                }
            }

            Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-fail');
            return $this->redirect('/profile/message');
        }

        $model = new TransferForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transfer = new Transfer();
            $account = $this->identity->entity->deposit;
            $transfer->from_account_id = $account->id;
            $account = Account::find()
                ->where('user_id = :user_id AND type = :type', [
                    ':user_id' => $model->to_user_id,
                    ':type' => Account::TYPE_DEPOSIT,
                ])
                ->one();
            $transfer->to_account_id = $account->id;
            $transfer->amount = $model->amount;
            $transfer->message = $model->message;

            if ($transfer->save()) {
                Email::send('confirm-transfer', $this->identity->entity->email, ['url' => $transfer->url]);
                Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-success');
            } else {
                Yii::$app->session->setFlash('profile-message', 'profile-account-transfer-fail');
            }

            return $this->redirect('/profile/message');
        }

        $toUserFullName = '(Кликните, чтобы задать пользователя)';
        if ($model->to_user_id) {
            $user = User::findOne($model->to_user_id);
            if ($user) {
                $toUserFullName = $user->fullName;
            }
        }

        return $this->render('transfer', [
            'title' => 'Перевести пользователю сайта',
            'model' => $model,
            'user' => $this->identity->entity,
            'toUserFullName' => $toUserFullName,
        ]);
    }
}
