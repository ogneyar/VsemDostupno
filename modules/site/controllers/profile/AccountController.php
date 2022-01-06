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
use app\models\Member;
use app\models\Partner;
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
                && $account->type != Account::TYPE_SUBSCRIPTION 
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
        if ($user->role == User::ROLE_PARTNER) {

            $partner_id = Partner::findOne(['user_id' => $user->id])->id;
            // можно так
            $members = Member::findAll(['partner_id' => $partner_id]);
            // или так
            // $members = Member::find()->where(['partner_id' => $partner_id])->all();
            // или вот так
            // $members = Member::find()->where('partner_id = :partner_id', [':partner_id' => $partner_id])->all();
            
            $total = 0;
            if ($members) {
                foreach ($members as $member) {
                    $total += Account::find()->where(['user_id' => $member->user_id])->andWhere(['type' => Account::TYPE_DEPOSIT])->one()->total;
                }
            }
            
            $groupAccounts[] = [
                'name' => 'Общая сумма расчётных счётов группы',
                'total' => $total,
                'members' => $members,
                'actionEnable' => false,
            ];

            $groupAccounts[] = [
                'name' => 'Общая сумма членских взносов группы',
                'total' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_GROUP_FEE)->total, 
                'members' => null,
                'actionEnable' => false,
            ];

        }

        $fraternityAccount = [];
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            $fraternityAccount[] = [
                'name' => 'Отчисленно в фонд содружества',
                'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_FRATERNITY),
                'actionEnable' => false,
            ];
        }

        $accountType = Yii::$app->getRequest()->getQueryParam('type');
        if ($accountType == Account::TYPE_RECOMENDER) {

        }else if (!$user->getAccount($accountType)) {
            $accountType = Account::TYPE_DEPOSIT;
        }

        $subscription = [
            'name' => 'Ежемесячные членские взносы',
            'account' => Yii::$app->user->identity->entity->getAccount(Account::TYPE_SUBSCRIPTION),
            'actionEnable' => false,
        ];

        // Рекомендательский сбор идёт на Инвестиционный счёт (TYPE_BONUS)
        $account_id = $user->getAccount(Account::TYPE_BONUS)->id;
        $info[] = [
            'name' => "Рекомендательские взносы",
            'actionEnable' => false,
            'recomender' => true,
            'dataProvider' => new ActiveDataProvider([
                'id' => Account::TYPE_RECOMENDER,
                'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account->id])->andWhere('message = "Рекомендательские взносы"'), 
                'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                'pagination' => [
                    'params' => array_merge($_GET, [
                        'type' => Account::TYPE_RECOMENDER,
                    ]),
                ],
            ]),
        ];
        // Членские взносы идут с Расчётного счёта (TYPE_DEPOSIT)
        $account_id = $user->getAccount(Account::TYPE_DEPOSIT)->id;
        $info[] = [
            'name' => "Членские взносы",
            'actionEnable' => false,
            'dataProvider' => new ActiveDataProvider([
                'id' => Account::TYPE_SUBSCRIPTION,
                'query' => AccountLog::find()->where('account_id = :account_id', [':account_id' => $account_id])->andWhere('message = "Членский взнос"'),
                'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
                'pagination' => [
                    'params' => array_merge($_GET, [
                        'type' => Account::TYPE_SUBSCRIPTION,
                    ]),
                ],
            ]),
        ];

        return $this->render('index', [
            'title' => 'Счета',
            'myAccounts' => $myAccounts,
            'groupAccounts' => $groupAccounts,
            'fraternityAccount' => $fraternityAccount,
            'subscription' => $subscription,
            'info' => $info,
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
