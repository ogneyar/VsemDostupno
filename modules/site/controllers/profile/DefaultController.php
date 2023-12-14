<?php

namespace app\modules\site\controllers\profile;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\base\Exception;
use app\modules\site\controllers\BaseController;
use app\modules\site\models\profile\LoginForm;
use app\modules\site\models\profile\RegisterForm;
use app\modules\site\models\profile\RegisterSmallForm;
use app\modules\site\models\profile\ForgotRequestForm;
use app\modules\site\models\profile\ForgotChangeForm;
use app\models\User;
use app\models\Member;
use app\models\Page;
use app\models\Email;
use app\models\Register;
use app\models\Forgot;
use app\models\Cart;
use app\models\Account;
use app\models\Provider;
use app\models\ProviderRegData;
use app\models\ProviderHasCategory;
use app\models\Candidate;
use app\models\NoticeEmail;
use app\models\Category;
use app\models\EmailLetters;


class DefaultController extends BaseController
{

    public function behaviors()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];

        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'login',
                            'register',
                            'forgot-change',
                            'forgot-request',
                            'message',
                            'register-provider',    
                            'register-telegram',    
                            'register-small',    
                        ],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => false,
                        'actions' => [
                            'forgot-change',
                            'forgot-request',   
                        ],
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            $config = require(__DIR__ . '/../../../../config/urlManager.php');
                            $baseUrl = $config['baseUrl'];
                            return $action->controller->redirect($baseUrl . 'profile');
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'index',
                            'message',           
                            'email',                                      
                            'delete-letter',
                            'read-letter',
                        ],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $config = require(__DIR__ . '/../../../../config/urlManager.php');
                            $baseUrl = $config['baseUrl'];
                            if (in_array(Yii::$app->user->identity->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])) {
                                $action->controller->redirect($baseUrl . 'admin')->send();
                                exit();
                            }

                            if (Yii::$app->user->identity->entity->disabled) {
                                $action->controller->redirect($baseUrl . 'profile/logout')->send();
                                exit();
                            }

                            return true;
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'logout',
                        ],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    
    public function actionIndex()
    {        
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];

        return $this->redirect($baseUrl);

    }



    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect($this->defaultRoute);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) { 
            if (Yii::$app->getUser()->getReturnUrl() == '/') {
                Yii::$app->getUser()->setReturnUrl(Url::to([$this->defaultRoute]));
            }

            return $this->goBack();
        } else {
            $model->password = '';
            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
            
            return $this->render('login', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
            ]);
        }
    }

    public function actionLogout()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];

        $cart = new Cart();
        $products = $cart->products;
        $cart->clear();

        Yii::$app->user->logout();

        foreach ($products as $product) {
            $cart->add($product, $product->cart_quantity);
        }

        return $this->redirect($baseUrl . 'profile/login');
    }

    public function actionRegister()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];
        
        $get = Yii::$app->request->get();
        if (isset($get['token'])) {
            $register = Register::findOne(['token' => $get['token']]); 

            if ($register && $register->user->disabled) {
                $register->user->disabled = 0;
                $register->user->save();
                $register->delete();

                if ($register->user->tg_id) {
                    Email::tg_send('active-profile-tg', $register->user->tg_id, [
                        'firstname' => $register->user->firstname,
                        'patronymic' => $register->user->patronymic,
                        'url' => Url::to(['/profile/member/personal'], true),
                        'reg_number' => $register->user->number,
                    ]);
                }else {
                    Email::send('active-profile', $register->user->email, [
                        'firstname' => $register->user->firstname,
                        'patronymic' => $register->user->patronymic,
                        'url' => Url::to(['/profile/member/personal'], true),
                        'reg_number' => $register->user->number,
                    ]);
                }
                
                Yii::$app->session->setFlash('profile-message', 'profile-register-success');
                return $this->redirect($baseUrl . 'profile/message');
            }

            Yii::$app->session->setFlash('profile-message', 'profile-register-fail');
            return $this->redirect($baseUrl . 'profile/message');
        }

        $model = new RegisterForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) { 
        // if ($model->load(Yii::$app->request->post())) {

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user = new User();
                $user->role = User::ROLE_MEMBER;
                $user->password = $model->password;
                $user->password_repeat = $model->password_repeat;
                $user->email = $model->email;
                $user->phone = '+' . preg_replace('/\D+/', '', $model->phone);
                $user->ext_phones = $model->ext_phones;
                $user->firstname = $model->firstname;
                $user->lastname = $model->lastname;
                $user->patronymic = $model->patronymic;
                $user->created_ip = Yii::$app->getRequest()->getUserIP();
                $user->birthdate = date('Y-m-d', strtotime($model->birthdate));
                $user->citizen = $model->citizen;
                $user->registration = $model->registration;
                $user->residence = $model->residence && $model->residence != $model->registration ? $model->residence : null;
                $user->passport = preg_replace('/\D+/', '', $model->passport);
                $user->passport_date = date('Y-m-d', strtotime($model->passport_date));
                $user->passport_department = $model->passport_department;
                $model->itn = preg_replace('/\D+/', '', $model->itn);
                $user->itn = $model->itn ? $model->itn : null;
                $user->skills = $model->skills ? $model->skills : null;
                $user->recommender_info = $model->recommender_info ? $model->recommender_info : null;

                $recommender = User::findOne(['number' => $model->recommender_id]); 
                $user->recommender_id = $recommender->id ? $recommender->id : 95;

                $user->re_captcha = $model->re_captcha;

                if (isset($get['tg']) && $get['tg'] != "false") $user->tg_id = $get['tg'];
                else $user->tg_id = "";
                
                if (!$user->save()) {
                    throw new Exception('Ошибка создания пользователя!');
                }

                $member = new Member();
                $member->partner_id = $model->partner;
                $member->user_id = $user->id;
                if (!$member->save()) {
                    throw new Exception('Ошибка создания участника!');
                }

                $register = new Register();
                $register->user_id = $user->id;
                if (!$register->save()) {
                    throw new Exception('Ошибка при регистрации пользователя!');
                }

                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION];
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    if (!$account->save()) {
                        throw new Exception('Ошибка создания счета пользователя!');
                    }
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                Yii::$app->session->setFlash('profile-message', 'profile-register-fail');
                return $this->redirect($baseUrl . 'profile/message');
                //throw new ForbiddenHttpException($e->getMessage());
            }

            /*Email::send('notify-registered-new-user', Yii::$app->params['adminEmail'], [
                'name' => $user->fullName,
                'viewUrl' => Url::to(['/admin/member/view', 'id' => $member->id], true),
                'updateUrl' => Url::to(['/admin/member/update', 'id' => $member->id], true),
            ]);*/
            
            $c_params = [
                'email' => $user->email,
            ];
            $candidate = Candidate::isCandidate($c_params);
            if ($candidate) {
                Email::send('register-candidate', Yii::$app->params['superadminEmail'], [
                    'link' => $candidate
                ]);
                Email::tg_send('register-candidate-tg', Yii::$app->params['superadminChatId'], [
                    'link' => $candidate
                ]);
            }            
            
            if ($emails = NoticeEmail::getEmails()) {
                // Email::send('admin-entity-request', $emails, [
                //     'fio' => $user->fullName,
                // ]);
                Email::tg_send('admin-entity-request-tg', Yii::$app->params['adminChatId'], [
                    'fio' => $user->fullName,
                ]);
            }

            if (isset($get['tg']) && $get['tg'] == "false" || ! isset($get['tg'])) {
                Email::send('entity-request', $user->email, [
                    'fio' => $user->respectedName,
                    'u_role' => 'Участника'
                ]);                

                Yii::$app->session->setFlash('profile-message', 'profile-entity-request');
                return $this->redirect($baseUrl . 'profile/message');
                
            }else {
                Email::tg_send('entity-request-tg', $user->tg_id, [
                    'fio' => $user->respectedName,
                    'u_role' => 'Участника'
                ]);

                Yii::$app->session->setFlash('profile-message', 'profile-entity-request-tg');
                return $this->redirect($baseUrl . 'profile/message');
            }
        } else {
            
            $model->password =
            $model->password_repeat = '';
            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();

            return $this->render('register', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
                'get' => $get,
            ]);
        }
    }

    public function actionForgotRequest()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];
        
        $model = new ForgotRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = User::findOne(['email' => $model->email, 'disabled' => 0]);

            if (!$user) {
                Yii::$app->session->setFlash('profile-message', 'profile-forgot-fail');
                return $this->redirect($baseUrl . 'profile/message');
            }

            $forgot = Forgot::findOne(['user_id' => $user->id]);
            if (!$forgot) {
                $forgot = new Forgot();
                $forgot->user_id = $user->id;
            }
            $forgot->save();

            Email::send('forgot', $user->email, ['url' => $forgot->url]);

            Yii::$app->session->setFlash('profile-message', 'profile-forgot-finish');
            return $this->redirect($baseUrl . 'profile/message');
            
        // }else if (false) {

        }else {
            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
            return $this->render('forgot-request', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
            ]);
        }
    }

    public function actionForgotChange()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];
        
        $get = Yii::$app->request->get();
        if (isset($get['token'])) {
            $token = $get['token'];
        } else {
            $post = Yii::$app->request->post();
            if (isset($post['token'])) {
                $token = $post['token'];
            }
        }

        $forgot = Forgot::findOne(['token' => $token]);
        if (!$forgot || $forgot->user->disabled) {
            throw new ForbiddenHttpException('Действие не разрешено.');
        }

        $model = new ForgotChangeForm(['token' => $token]);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) { 

            $forgot->user->password = $model->password;
            $forgot->user->password_repeat = $model->password_repeat;
            // $forgot->user->scenario = 'admin_creation';
            $forgot->user->scenario = 'user_login';
            // var_dump($forgot->user->password);
            $forgot->user->save();
            // echo("<script>console.log('".$forgot->user->password."')</script>");
            $forgot->delete();

            Yii::$app->session->setFlash('profile-message', 'profile-forgot-success');
            return $this->redirect($baseUrl . 'profile/message');
        } else {
            $model->password =
            $model->password_repeat = '';

            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
            return $this->render('forgot-change', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
            ]);
        }
    }

    public function actionMessage()
    {
        $name = Yii::$app->session->getFlash('profile-message');
        $model = Page::findOne(['slug' => $name]);
        if (!$model) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
        return $this->render('register-message', [
            'model' => $model,
            'menu_first_level' => $menu_first_level ? $menu_first_level : [],
        ]);
    }


    public function actionRegisterProvider()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];

        $request = Yii::$app->request;
        $get = $request->get();
        
        $model_provider = new Provider();
        $model_user = new User();
        if ($reg_tmp = ProviderRegData::getStepByIp(Yii::$app->getRequest()->getUserIP())) {
            $model = $reg_tmp;
            $step = $reg_tmp['step'];
            if ($step == 2) {
                $model->scenario = 'reg_step_2';
            }
        } else {
            $model = new ProviderRegData();
            $model->scenario = "reg_step_1";
            $step = 1;
        }
        
        if (Yii::$app->request->post('reg_step')) {
            switch (Yii::$app->request->post('reg_step')) {
                case 1:
                    if ($model->load(Yii::$app->request->post())) {
                        $model->ip = Yii::$app->getRequest()->getUserIP();
                        $model->step = 2;
                        
                        if ($model->save()) {
                            $step = 2;
                            $model->scenario = 'reg_step_2';
                        }
                    }
                break;
                case 2:
                    if ($model->load(Yii::$app->request->post())) {
                        $model->step = 3;
                        
                        if ($model->save()) {
                            $step = 3;
                        }
                    }
                break;
                case 3:
                    if ($model_user->load(Yii::$app->request->post())) {
                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            $model_user->role = User::ROLE_PROVIDER;
                            $model_user->disabled = 0;
                            $model_user->phone = $model->phone;
                            $model_user->firstname = $model->firstname;
                            $model_user->lastname = $model->lastname;
                            $model_user->patronymic = $model->patronymic;
                            $model_user->created_ip = $model->ip;
                            $model_user->birthdate = $model->birthdate;
                            $model_user->citizen = $model->citizen;
                            $model_user->registration = $model->registration;
                            $model_user->passport = $model->passport;
                            $model_user->passport_date = $model->passport_date;
                            $model_user->passport_department = $model->passport_department;
                            $model_user->itn = $model->itn;
                            $model_user->ext_phones = $model->ext_phones;
                            
                            $recommender = User::findOne(['number' => $model->recommender_id]); 
                            $model_user->recommender_id = $recommender->id ? $recommender->id : 95;
                            
                            
                            if (isset($get['tg']) && $get['tg'] != "false") $model_user->tg_id = $get['tg'];
                            else $model_user->tg_id = "";
                

                            if (!$model_user->save()) {
                                throw new Exception('Ошибка создания пользователя!');
                            }
                            
                            $model_provider->scenario = 'self_reg';
                            $model_provider->user_id = $model_user->id;
                            $model_provider->name = $model->name;
                            $model_provider->field_of_activity = $model->field_of_activity;
                            $model_provider->legal_address = $model->legal_address;
                            $model_provider->snils = $model->snils;
                            $model_provider->ogrn = $model->ogrn;
                            $model_provider->site = $model->site;
                            
                            if (!$model_provider->save()) {
                                throw new Exception('Ошибка создания поставщика!');
                            }
                            

                            $member = new Member();
                            $member->partner_id = $model->partner;
                            $member->user_id = $model_user->id;
                            $member->become_provider = 1;
                            if (!$member->save()) {
                                throw new Exception('Ошибка создания участника!');
                            }



                            foreach (json_decode($model->category) as $cat) {
                                $category = new ProviderHasCategory;
                                $category->provider_id = $model_provider->id;
                                $category->category_id = $cat;
                                if (!$category->save()) {
                                    throw new Exception('Ошибка создания категорий!');
                                }
                            }
                            
                            $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION];
                            foreach ($types as $type) {
                                $account = new Account(['user_id' => $model_user->id, 'type' => $type, 'total' => 0]);
                                if (!$account->save()) {
                                    throw new Exception('Ошибка создания счета пользователя!');
                                }
                            }
                            
                            $model->delete();
                            
                            $transaction->commit();
                        } catch (Exception $e) {
                            $transaction->rollBack();
                            
                            //Yii::$app->session->setFlash('profile-message', 'profile-register-fail');
                            //return $this->redirect(self::WEB . '/profile/message');
                            throw new ForbiddenHttpException($e->getMessage());
                        }
                        
                        $c_params = [
                            'email' => $model_user->email,
                        ];
                        $candidate = Candidate::isCandidate($c_params);
                        if ($candidate) {
                            // Email::send('register-candidate', Yii::$app->params['superadminEmail'], [
                            //     'link' => $candidate
                            // ]);
                            Email::tg_send('register-candidate-tg', Yii::$app->params['superadminChatId'], [
                                'link' => $candidate
                            ]);
                        }
                        
                        /*Email::send('notify-registered-new-provider', Yii::$app->params['adminEmail'], [
                            'name' => $model_provider->name,
                            'viewUrl' => Url::to(['/admin/provider/view', 'id' => $model_provider->id], true),
                            'updateUrl' => Url::to(['/admin/provider/update', 'id' => $model_provider->id], true),
                        ]);*/

                        /*Email::send('active-profile-provider', $model_user->email, [
                            'firstname' => $model_user->firstname,
                            'patronymic' => $model_user->patronymic,
                            'email' => $model_user->email,
                            'password' => $_POST['User']['password'],
                            'reg_number' => $model_user->number,
                        ]);*/
                        
                        if (isset($get['tg']) && $get['tg'] == "false" || ! isset($get['tg'])) {
                            Email::send('entity-request', $model_user->email, [
                                'fio' => $model_user->respectedName,
                                'u_role' => 'Поставщика'
                            ]);
                        }else {
                            Email::tg_send('entity-request-tg', $get['tg'], [
                                'fio' => $model_user->respectedName,
                                'u_role' => 'Поставщика'
                            ]);
                        }

                        if ($emails = NoticeEmail::getEmails()) {
                            // Email::send('admin-entity-request', $emails, [
                            //     'fio' => $model_user->fullName,
                            // ]);
                            Email::send('admin-entity-request-tg', Yii::$app->params['adminChatId'], [
                                'fio' => $model_user->fullName,
                            ]);
                        }
                        
                        Yii::$app->session->setFlash('profile-message', 'profile-entity-request');
                        return $this->redirect($baseUrl . 'profile/message');
                    }
                break;
            }
        }
        
        $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();
        return $this->render('register-provider',[
           'model' => $model,
           'model_user' => $model_user,
           'step' => $step,
           'menu_first_level' => $menu_first_level ? $menu_first_level : [],
           'get' => $get,
        ]);
    }

    
    public function actionEmail()
    {        
        $user_data = [ 
            'id' => $this->identity->entity->id,
            'firstname' => $this->identity->entity->firstname,
        ];

        return $this->render('email', [
            'user_data' => $user_data,
        ]);

    }

    
    public function actionReadLetter()
    {        
        if (isset($_POST['id'])) {
            EmailLetters::readLetter($_POST['id']);                
        }
    }


    public function actionDeleteLetter()
    {        
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];

        if (isset($_POST['id'])) {
            EmailLetters::deleteLetter($_POST['id']);
            return $this->redirect($baseUrl . 'profile/email');
        }        

        return $this->redirect($baseUrl);

    }

    
    public function actionRegisterTelegram()
    {
        $request = Yii::$app->request;
        $get = $request->get();
        return $this->render('register-telegram', [
            'get' => $get,
        ]);
    }

    
    public function actionRegisterSmall()
    {
        $config = require(__DIR__ . '/../../../../config/urlManager.php');
        $baseUrl = $config['baseUrl'];
        
        $get = Yii::$app->request->get();
        if (isset($get['token'])) {
            $register = Register::findOne(['token' => $get['token']]); 

            if ($register && $register->user->disabled) {
                $register->user->disabled = 0;
                $register->user->save();
                $register->delete();

                if ($register->user->tg_id) {
                    Email::tg_send('active-profile-tg', $register->user->tg_id, [
                        'firstname' => $register->user->firstname,
                        'patronymic' => $register->user->patronymic,
                        'url' => Url::to(['/profile/member/personal'], true),
                        'reg_number' => $register->user->number,
                    ]);
                }else {
                    Email::send('active-profile', $register->user->email, [
                        'firstname' => $register->user->firstname,
                        'patronymic' => $register->user->patronymic,
                        'url' => Url::to(['/profile/member/personal'], true),
                        'reg_number' => $register->user->number,
                    ]);
                }
                
                Yii::$app->session->setFlash('profile-message', 'profile-register-success');
                return $this->redirect($baseUrl . 'profile/message');
            }

            Yii::$app->session->setFlash('profile-message', 'profile-register-fail');
            return $this->redirect($baseUrl . 'profile/message');
        }

        $model = new RegisterSmallForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
        // if ($model->load(Yii::$app->request->post())) {

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $user = new User();
                $user->role = $get["role"] == "provider" ? User::ROLE_PROVIDER : User::ROLE_MEMBER;
                $user->password = $model->password;
                $user->password_repeat = $model->password_repeat;
                $user->email = "tg".$get['tg']."@mail.ru";
                $user->phone = $model->phone;
                $user->ext_phones = "";
                $user->firstname = $model->firstname;
                $user->lastname = $get["role"] == "provider" ? $model->lastname : "lastname";
                $user->patronymic = $model->patronymic;
                $user->created_ip = Yii::$app->getRequest()->getUserIP();
                $user->citizen = "citizen";
                $user->registration = "registration";
                $user->passport = null;
                $user->passport_department = "passport_department";
                $user->passport_date = null;
                $user->birthdate = null;
                $user->residence = null;
                $user->itn = null;
                $user->skills = null;
                $recommender = User::findOne(['number' => 40]); 
                $user->recommender_id = $recommender->id ? $recommender->id : 3;
                $user->recommender_info = null;
                $user->re_captcha = $model->re_captcha;                
                // $user->scenario = 'admin_creation';

                if (isset($get['tg']) && $get['tg'] != "false") $user->tg_id = $get['tg'];
                else $user->tg_id = "";
                
                if (!$user->save()) { 
                    // Email::tg_send('entity-request-tg', Yii::$app->params['master'], [
                    //     'fio' => 'name',
                    //     'u_role' => 'хз password: '.$model->password
                    // ]);
                    throw new Exception('Ошибка создания пользователя!');
                }

                if ($get["role"] == "provider") {
                    $provider = new Provider();
                    $provider->user_id = $user->id;
                    $provider->name = $user->firstname;
                    $provider->description = $model->description;
                    if (!$provider->save()) {
                        throw new Exception('Ошибка создания поставщика!');
                    }
                }else { 
                    $member = new Member();
                    $member->partner_id = $model->partner;
                    $member->user_id = $user->id;
                    if (!$member->save()) {
                        throw new Exception('Ошибка создания участника!');
                    }
                }

                $register = new Register();
                $register->user_id = $user->id;
                if (!$register->save()) {
                    throw new Exception('Ошибка при регистрации пользователя!');
                }

                $types = [Account::TYPE_DEPOSIT, Account::TYPE_BONUS, Account::TYPE_SUBSCRIPTION];
                foreach ($types as $type) {
                    $account = new Account(['user_id' => $user->id, 'type' => $type, 'total' => 0]);
                    if (!$account->save()) {    
                        throw new Exception('Ошибка создания счета пользователя!');
                    }
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                // Yii::$app->session->setFlash('profile-message', 'profile-register-fail');
                // return $this->redirect($baseUrl . 'profile/message');
                throw new ForbiddenHttpException($e->getMessage());
            }

            $c_params = [
                'email' => $user->email,
            ];
            $candidate = Candidate::isCandidate($c_params);
            if ($candidate) {
                if ($get["role"] == "provider") {
                    Email::tg_send('register-candidate-provider-tg', Yii::$app->params['superadminChatId'], [
                        'link' => $candidate
                    ]);   
                }else {
                    Email::tg_send('register-candidate-tg', Yii::$app->params['superadminChatId'], [
                        'link' => $candidate
                    ]);                    
                }

            }            
            if ($get["role"] == "provider") {
                Email::tg_send('registr-small-provider', Yii::$app->params['superadminChatId'], [
                    'tg_id' => $user->tg_id,
                    'name' => $user->getRespectedName(),
                    'phone' => $user->phone,
                ]);
            }else {
                Email::tg_send('registr-small', Yii::$app->params['superadminChatId'], [
                    'tg_id' => $user->tg_id,
                    'name' => $user->getRespectedName(),
                    'phone' => $user->phone,
                ]);
            }

            if ($get["role"] != "provider") {
                Email::tg_send('entity-request-tg', $user->tg_id, [
                    'fio' => $user->respectedName,
                    'u_role' => 'Поставщика'
                ]);
            }else {
                Email::tg_send('entity-request-tg', $user->tg_id, [
                    'fio' => $user->respectedName,
                    'u_role' => 'Участника'
                ]);
            }

            Yii::$app->session->setFlash('profile-message', 'profile-entity-request-tg');
            return $this->redirect($baseUrl . 'profile/message');              


        } else {
            $model->password =
            $model->password_repeat = '';
            $menu_first_level = Category::find()->where(['parent' => 0, 'visibility' => 1])->all();

            if ($get["role"] && $get["role"] == "provider") {
                $array = explode('|', $get["fio"]);
                
                $lastname = $array[0]; // фамилия
                $model->lastname = $lastname;
                $firstname = $array[1]; // имя
                $model->firstname = $firstname;
                $patronymic = $array[2]; // отчество
                $model->patronymic = $patronymic;

                $model->phone = $get["phone"];
            }

            return $this->render('register-small', [
                'model' => $model,
                'menu_first_level' => $menu_first_level ? $menu_first_level : [],
                'get' => $get,
            ]);
        }
    }

}
