<?php

namespace app\modules\bots\controllers;

use Yii;
use DateTime;
use yii\web\Controller;
use yii\web\Response;
use app\modules\mailing\models\MailingVoteStat;
use app\modules\bots\api\Bot;
use app\models\User;
use app\models\Forgot;
use app\models\Email;
use app\models\Account;
use app\models\TgCommunication;
use app\models\Service;
use app\models\Category;
use app\models\CategoryHasService;



class DefaultController extends Controller
{
    // можно так отключить проверку csrf-токена
    public $enableCsrfValidation = false;

    // обработка экшинов до запуска
    // public function beforeAction($action)
    // {
    //     if ($action->id == 'index') {
    //         // а, можно так отключить проверку csrf-токена, для конкретного экшена
    //         Yii::$app->controller->enableCsrfValidation = false;
    //     }

    //     return parent::beforeAction($action);
    // }    
    
    public function actionIndex()
    {
        $config = require(__DIR__ . '/../../../config/constants.php');
        $web = $config['WEB'];
        $token = $config['BOT_TOKEN'];

        $master = Yii::$app->params['masterChatId']; 
        
        // $admin = $master; 
        $admin = Yii::$app->params['adminChatId'];

        $bot = new Bot($token);

        $request = Yii::$app->request;
        
        $get = $request->get();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post = $request->post();
            if ($post) {
                $bot->sendMessage($master, "пост");
            }else {
                $data = $bot->init('php://input');
                if ($data) {                     
                    // -----------------------------------------
                    // тут начинается обработка запросов бота!!!
                    // -----------------------------------------
                    $from_id = null;
                    if (isset($data['message'])) $from_id = $data['message']['from']['id'];
                    else if (isset($data['callback_query'])) $from_id = $data['callback_query']['from']['id'];
                    // if ($from_id && $from_id == $master) {
                    //     $bot->sendMessage($from_id, $bot->PrintArray($data));
                    // } 
                    $bot->sendMessage($master, $bot->PrintArray($data));
                    // -----------------------------------------
                    requestProcessing($bot, $master, $admin);
                    // -----------------------------------------
                }else {
                    $bot->sendMessage($master, "пост пуст");
                }
            }
            return "ok";
        }else {
            if ($get) {
                if (isset($get['url'])) {
                    $url = "https://xn----9sbegbr4cary4h.xn--p1acf/web/bots";
                    $response = $bot->setWebhook($url); // $get['url']
                    $bot->sendMessage($master, "set webhook");
                    return "set webhook";
                }else {
                    if (isset($get['message'])) {
                        $bot->sendMessage($master, $get['message']);
                    }else{
                        $bot->sendMessage($master, "гет");
                    }
                    return "Поступил гет запрос!";
                }
            }else {
                $bot->sendMessage($master, "гет пуст");
                return "Добро пожаловать!";
            }
        }

        
        $response = file_get_contents("https://api.telegram.org/bot". $token ."/getMe");
        // json_encode - из объекта в строку
        // json_decode - из строки в объект
        $response = json_decode($response, true);
        if ($response->ok) {            
            // mb_convert_encoding($str, "UTF8"); - перевод из юникода в UTF-8
            $first_name = mb_convert_encoding($response->result->first_name, "UTF8");
            return $first_name;
            // return json_encode($response->result);
            // return json_encode($response);
        }
        return "Ошибка!";        
    }

    //
    public function actionTest()
    {
        // клавиатура на линии, привязанная к сообщению
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Информация',
                        'callback_data' => 'information',
                        'url' => null,
                        'login_url' => null,
                        'switch_inline_query' => null,
                        'switch_inline_query_current_chat' => null,
                        'callback_game' => null,
                        'pay' => false
                    ]
                ]
            ]
        ];

        return "ok";
    }



}


function requestProcessing($bot, $master, $admin) {
    $data = $bot->data;

    if (isset($data['message'])) {
        requestMessage($bot, $data['message'], $master, $admin);
    }else if (isset($data['callback_query'])) {
        requestCallbackQuery($bot, $data['callback_query'], $master, $admin);
    }    
}


function requestMessage($bot, $message, $master, $admin) {

    $message_id = $message['message_id'];
    $from = $message['from'];
        $first_name = $from['first_name'];
    $chat = $message['chat'];
        $chat_id = $chat['id'];
    $text = $message['text'];

    if ($message['reply_to_message']) {
        $reply_to_message = $message['reply_to_message'];
        
        if ($reply_to_message['text']) {
            $reply_text = $reply_to_message['text'];
        }else 
        if ($reply_to_message['voice']) {
            $caption = $reply_to_message['caption'];
        }
    }

    if ($message['voice']) {
        $voice = $message['voice'];
        $file_id = $voice['file_id'];
    }


    /*******************
    
        ГЛАВНОЕ МЕНЮ

    ********************/
    if ($text == "/start" || $text == "Старт" || $text == "/menu" || $text == "Главное меню" || $text == "Назад" ||  $text == "🌟Главное меню")
    {    
        $send = "В голубом кружочке  с низу, в меню, Вы найдёте ссылки на всю необходимую информацию";
               
        $ReplyKeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Приветствие' ],
                    [ 'text' => 'О нас' ]
                ]
            ],
            'resize_keyboard' => true,
            'selective' => true,
        ];        
        $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);

        return;
    }

    //-----------------------------------------------------------------------
    $text_split = explode(" ", $text);

    if ($text_split[0] == "/start" && $text_split[1]) {
        if ($text_split[1] == "forgot") {

            $user = User::findOne(['tg_id' => $chat_id, 'disabled' => 0]);

            if (!$user) {
                $bot->sendMessage($chat_id, "Вы не зарегестрированны!");
                return;
            }

            $forgot = Forgot::findOne(['user_id' => $user->id]);
            if (!$forgot) {
                $forgot = new Forgot();
                $forgot->user_id = $user->id;
            }
            $forgot->save();

            Email::tg_send('forgot-tg', $chat_id, ['url' => $forgot->url]);

            return;

        }else {

            $send = "Здравствуй " . $first_name . "!\r\n\r\n";
            $send .= "Добро пожаловать, это регистрация на сайте Будь-Здоров.рус.\r\n";
            $send .= "В боте Вы уже зарегестрированны. Для продолжения регистрации нажмите на кнопку ниже (прикреплена к этому сообщению).";
            $host = "https://будь-здоров.рус/web";
            // $host = "http://localhost:8080";
            if ($text_split[1] == "member") $action = "register";
            else if ($text_split[1] == "provider") $action = "register-provider";
            $url = "$host/profile/$action?tg=$chat_id";
            $InlineKeyboardMarkup = [
                'inline_keyboard' => [[[
                    'text' => 'Продолжить',
                    'url' => "$url"
                ]]]
            ];
            $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

            return;
                
        }
    }
    //-----------------------------------------------------------------------


    /******************
    
           ТЕСТ

    *******************/
    if ($text == "Тест" || $text == "/test")
    {
        $send = "Вы зашли на тестовую страницу, отправьте запрос тестовому пользователю.";
    
        $tgCom = TgCommunication::findOne(['chat_id' => $chat_id]);

        if (!$tgCom) {
            $tgCom = new TgCommunication();
        }
            
        $tgCom->chat_id = $chat_id;
        $tgCom->to_chat_id = $master;
        // $tgCom->to_chat_id = $admin;
        
        if ( ! $tgCom->save() ) {            
            $send = "Ошибка создания/сохранения экземпляра класса TgCommunication!";
            $bot->sendMessage($chat_id, $send);
            // throw new Exception($send);
        }

        $bot->sendMessage($chat_id, $send);

        return;
    }

    /********************
    
           ПОМОЩЬ

    *********************/
    if ($text == "Помощь" || $text == "/help")
    {
        $send = "Вы зашли на страницу обратной связи, выбирите нужное действие.";
    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Специалисты' ],
                    // [ 'text' => 'Проголосовать' ],
                ],
                [
                    [ 'text' => 'Задать вопрос админу' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);

        return;
    }


    /********************
    
           СПЕЦИАЛИСТЫ

    *********************/
    if ($text == "Специалисты" || $text == "/specialists")
    {
        // $services = Service::find(['tg_visible' => 1])->all();
        $services = Service::find()->where(['tg_visible' => 1])->all();

        $inline_keyboard = [];

        foreach ($services as $i => $value) {
            $service = $services[$i];

            $categoryHasService = CategoryHasService::findOne(['service_id' => $service->id]);

            if ($categoryHasService) {
                
                $category_id = $categoryHasService->category_id;
    
                $category = Category::findOne(['id' => $category_id]);
            
                array_push($inline_keyboard, [
                    [
                        'text' => $category->name,
                        'callback_data' => 'specialists_' . $category->id
                    ]
                ]);

            }
        }

        $send = "В разделе “Специалисты” Вы можете получить помощь нужного специалиста.";
    
        $InlineKeyboardMarkup = [
            'inline_keyboard' => $inline_keyboard
        ];

        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /********************
    
           ПРОГОЛОСОВАТЬ

    *********************/
    if ($text == "Проголосовать" || $text == "/vote")
    {
        $send = "Ещё не реализованно!";

        $bot->sendMessage($chat_id, $send);

        $send = "Тест редактирования сообщения вместе с кнопками";
    
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Начать тест',
                'callback_data' => 'test_edit'
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);


        return;
    }



    /***********************************
    
           ЗАДАТЬ ВОПРОС АДМИНУ

    ************************************/
    if ($text == "Задать вопрос админу" || $text == "/question")
    {
        $send = "Здесь Вы можете задать свой вопрос, пожаловаться на нашу работу или внести своё предложение. Внесите текст в строку сообщения и отправьте его нам.";
        // $send = "Вы в любое время можете задать свой вопрос, пожаловаться на нашу работу или внести своё предложение отправив текстовое или голосовое сообщения.\r\n\r\nПосле отправки Вам придёт сообщение с вопросом: 'Вы желаете задать вопрос?', подтвердите нажав кнопку 'Да'. Если передумали или не верно написали, нажмите 'Нет'.";
           
        $HideKeyboard = [
            'hide_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $HideKeyboard);

        return;
    }


    
    /***********************
    
           ИНФОРМАЦИЯ

    ************************/
    if ($text == "Информация" || $text == "/info")
    {
        $send = "В разделе Информация, Вы можете узнать баланс своих счетов а так же восполнить информацию о нашей деятельности.";
    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Баланс' ],
                    [ 'text' => 'Общее' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);

        return;
    }
    

    /***********************
    
           БАЛАНС

    ************************/
    if ($text == "Баланс" || $text == "/balance")
    {
        
        $user = User::findOne(['tg_id' => $chat_id, 'disabled' => 0]);

        if (!$user) {
            $bot->sendMessage($chat_id, "Для получения выписки со счёта Вам необходимо пройти регистрацию.");
            return;
        }

        $account = Account::findOne(['id' => $user->id]);
        
        $face = $user->getAccount(Account::TYPE_DEPOSIT); // расчётный (лицевой) счёт
        $invest = $user->getAccount(Account::TYPE_BONUS); // инвестиционный счёт
        $partner = $user->getAccount(Account::TYPE_STORAGE); // партнёрский счёт
        $pay = $user->getAccount(Account::TYPE_SUBSCRIPTION); // членский взнос
        
        

        $send = "*Доброго времени суток,\r\n    ".$user->firstname." ".$user->patronymic."!!!*\r\n\r\n";

        if ($user->role == User::ROLE_ADMIN) {
            $send .= "Вы же администратор, какой вам счёт?";

            $bot->sendMessage($chat_id, $send, "markdown");
            return;
        }

        $send .= "Предоставляем выписку по Вашему счету.\r\n";

        if ($user->role == User::ROLE_MEMBER) {         
            if ($user->lastname == "lastname") { // пройдена упрощённая регистрация   
                $send .= "*Не зарегистрированный участник:*\r\n";
            }else {
                $send .= "*Пайщик - Участник:*\r\n";
            }
        }
        else
        if ($user->role == User::ROLE_PARTNER) {
            $send .= "*Пайщик - Партнёр:*\r\n";
        }
        else
        if ($user->role == User::ROLE_PROVIDER) {           
            $send .= "*Пайщик - Поставщик:*\r\n";
        }

        $send .= "Лицевой счёт:\r\n    ".formatPrice($face->total)."\r\n";
        $send .= "Инвестиционный счёт:\r\n    ".formatPrice($invest->total);

        if ($user->role == User::ROLE_MEMBER) {
            if ($user->lastname == "lastname") $send .= "\r\n*Накопительный счёт не задействован.*";
        }

        $send .= "\r\n";

        if ($user->role == User::ROLE_PARTNER) {
            $send .= "Партнёрский счёт:\r\n    ".formatPrice($partner->total)."\r\n";
        }
        
        if ( ! ($user->role == User::ROLE_MEMBER && $user->lastname == "lastname")) {
            $send .= "Ежемесячный паевой взнос: ";
                
            $d = new DateTime();
            $date = $d->format('t.m.Y');
    
            if ($pay->total > 0) $send .= "*Не внесён*";
            else $send .= "*Внесён до ".$date.".*";
        }


        $bot->sendMessage($chat_id, $send, "markdown");

        return;
    }
    

    /***********************
    
           ОБЩЕЕ

    ************************/
    if ($text == "Общее" || $text == "/general")
    {
        $send = "Ознакомтесь с полезной информацией по нашим Программам и о Кооперации в целом.";
    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Наши программы' ],
                    [ 'text' => 'Кооперация' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);

        return;
    }
    

    /***********************
    
           ПРОГРАММЫ

    ************************/
    if ($text == "Наши программы" || $text == "/programs")
    {
        
        $send = "Уважаемый пользователь.";

        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Кооперация' ],
                    [ 'text' => 'Назад' ],
                ]
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, "markdown", $KeyboardMarkup);

        $send = "Выбирите интересующующие Вас программы для участия.";
            
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Разумный подход',
                'callback_data' => 'program_reasonable'
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }
    

    /***********************
    
           КООПЕРАЦИЯ

    ************************/
    if ($text == "Кооперация" || $text == "/cooperation")
    {
        $send = "Бизнес или кооперация";
    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Наши программы' ],
                    [ 'text' => 'Назад' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);
        
        $send = "Мы рады представить Вам нашу мини-книгу, посвященную вопросам потребительских обществ в России.

Несмотря на то, что потребительские кооперативы все время у нас «на слуху», мало кто знает, что они из себя представляют. Максимум, что известно – что это общество, образованное пайщиками, чтобы вместе что-то делать или закупать.

И в основном все вопросы потребкооперации рассматриваются именно через призму пайщиков – как обменять пай, как правильно оформлять и платить взносы, переходит ли пай по наследству, и так далее.

Отрицать пользу и выгодность работы потребительских обществ для пайщиков, конечно, нельзя. Но те возможности, которые открываются перед организаторами, способны серьезно поменять Ваши представления о бизнесе.";
            
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str2'
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }
    

    /***********************
    
           РЕГИСТРАЦИЯ

    ************************/
    if ($text == "/regist" || $text == "Регистрация" || $text == "Шаг назад")
    {
        $send = "Существует два возможных варианта регистрации на сайте Будь-здоров.рус:

            1.    Упрощённая 
            2.    Полная

        Упрощённая регистрация позволяет Вам делать заказы из личного кабинета на сайте, но без предоставления скидок и накоплений.
        
        Что бы узнать какие возможности даёт “[Полная регистрация](https://будь-здоров.рус/web/category/454)” 👈 пройдите по ссылке.";
        
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Упрощённая' ],
                    [ 'text' => 'Полная' ],
                ],
                [
                    [ 'text' => 'Главное меню' ],
                ]
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, "markdown", $KeyboardMarkup);

        return;
    }


    /***********************
    
     УПРОЩЁННАЯ регистрация

    ************************/
    if ($text == "Упрощённая")
    {
        $send = "Уважаемый пользователь.";

        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Шаг назад' ],
                ]
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, "markdown", $KeyboardMarkup);

        $send = "Перейдя к дальнейшей регистрации, выберите удобное место (адрес) получения  заказов, укажите своё имя и отчество, а так же  телефон для связи.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Перейти к дальнейшей регистрации',
                'url' => "https://Будь-здоров.рус/web/profile/register-small?tg=".$chat_id
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    
    /*******************
    
     ПОЛНАЯ регистрация

    ********************/
    if ($text == "Полная")
    {
        $send = "Уважаемый пользователь.";

        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Шаг назад' ],
                ]
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, "markdown", $KeyboardMarkup);
        
        $send = "Перейдя к дальнейшей регистрации, введите все обязательные данные, они помечены красной звёздочкой.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Перейти к дальнейшей регистрации',
                'url' => "https://Будь-здоров.рус/web/profile/register?tg=".$chat_id
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }
        
    
    /*********
    
     НОВИЧКАМ

    *********/
    if ($text == "/newbie" || $text == "Новичкам" || $text == "/new")
    {    
        $send = "Дорогой друг, мы приветствуем тебя на нашем общем и увлекательном проекте. 🌈
        Сердечно ❤️ БлагоДарим тебя за принятое решение, присоединиться. 
        Вместе мы сможем большее!🌟
        
        ";
               
        $ReplyKeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Информация' ],
                    [ 'text' => 'Регистрация' ]
                ],
                [
                    [ 'text' => '🌟Главное меню' ]
                ]
            ],
            'resize_keyboard' => true,
            'selective' => true,
        ];        
        $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);

        $send = "Ниже, в \"Меню\" ты сможешь найти всю, последовательность нужных тебе действий и пройти \"Регистрацию\".
        
        Чтобы узнать свой регистрационный номер для связи через Телеграмм канал, нажми кнопку ниже  👇";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Узнать свой номер',
                'callback_data' => 'client_id'
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    /************
    
     ПРИВЕТСТВИЕ

    *************/
    if ($text == "/hello" || $text == "Приветствие")
    {    
        $file_id = "BAACAgIAAxkBAAIHGWTm_pIWtP7sItX4-diNDV-tgVGZAAL1MgACxgQ5S7UZOXKYTDdCMAQ";
               
        $ReplyKeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'О нас' ],
                    [ 'text' => 'Назад' ]
                ]
            ],
            'resize_keyboard' => true,
            'selective' => true,
        ];        
        $bot->sendVideo($chat_id, $file_id, null, null, $ReplyKeyboardMarkup);

        return;
    }

    /************
    
        О НАС

    *************/
    if ($text == "/about" || $text == "О нас")
    {    
        $send = "Коротко о нас.";
        
        $ReplyKeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Приветствие' ],
                    [ 'text' => 'Назад' ]
                ]
            ],
            'resize_keyboard' => true,
            'selective' => true,
        ];        
        $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);

        $send = "Потребительское общество (ПО) «Будь здоров» функционирует в с. Дмитриевы горы Меленковского района Владимирской обл., образовано в 2023 года. 
        Потребительское общество, является не коммерческой организацией и работает в формате клуба.
        Основными целями Общества являются:
        Пропаганда здорового образ жизни и здоровья для своих участников.
        Приоритетными вопросами Общества являются продовольственные и образовательные программы. 
         
        Производители отечественных (местных) товаров и услуг, предлагают качественную продукцию участникам Общества по доступным ценам.
        Общество со своей стороны осуществляет контроль и мониторинг цены и качества.";
               
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'about_str2'
            ]]]
        ];  
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);

        return;
    }
    


    /******************************************
    
        ЕСЛИ ПРИСЛАЛИ НЕИЗВЕСТНОЕ СООБЩЕНИЕ

    *******************************************/
    if ($chat_id != $admin) {
        
        $tgCom = TgCommunication::findOne(['chat_id' => $chat_id]);

        if ($tgCom) {
            $send = "Сообщение от пользователя №" . $chat_id . "\r\n\r\n" . $text;
            $bot->sendMessage($tgCom->to_chat_id, $send);

            $send = "Сообщение отправлено пользователю №" . $tgCom->to_chat_id;
            $bot->sendMessage($chat_id, $send);
    
            $tgCom->delete();

            return;
        }

        $send = "Вы желаете задать вопрос?";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[
                [
                    'text' => 'Да',
                    'callback_data' => 'question_yes'
                ],
                [
                    'text' => 'Нет',
                    'callback_data' => 'question_no'
                    ],
            ]]
        ];  
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup, $message_id);

    }else    
    // это для админа
    if ($reply_to_message) {
        if ($caption) {            
            // $caption = str_replace("\r\n", "", $caption);
            $reply_id = substr($caption, 0, strpos($caption, "Сообщение от клиента!"));
        }else
        if ($reply_text) {            
            // $reply_text = str_replace("\r\n", "", $reply_text);
            $reply_id = substr($reply_text, 0, strpos($reply_text, "Сообщение от клиента!"));
        }

        if ($reply_id) {
             if ($text) {
                $bot->sendMessage($reply_id, $chat_id . "\r\nСообщение от администратора!\r\n\r\n" . $text);
                $bot->sendMessage($admin, "Сообщение клиенту отправлено!");
            }else if ($voice) {
                $bot->sendVoice($reply_id, $file_id, $chat_id . "\r\nСообщение от администратора!");
                $bot->sendMessage($admin, "Сообщение клиенту отправлено!");
            }else {
                $bot->sendMessage($admin, "Можно отправлять только текстовые и голосовые сообщения!");
            }
        }
    }else {        
        $bot->sendMessage($chat_id, "Ваше сообщение НЕ БУДЕТ отправлено администратору!\r\n\r\nВы и есть администратор!!!");
    }



}





function formatPrice($price) {
    if (! $price || $price == 0) return "00 руб. 00";
    $floor_price = floor($price);
    $drobnaya = floor(($price - $floor_price)*100);
    if ($drobnaya < 10) $response = $floor_price . " руб. 0" . $drobnaya;
    else $response = $floor_price . " руб. " . $drobnaya;
    return $response;
}




/*
$ReplyKeyboardRemove = [
    'remove_keyboard' => true
];

$HideKeyboard = [
    'hide_keyboard' => true
];
*/
// [Полная регистрация](https://Будь-здоров.рус/web/profile/register?tg=".$chat_id.")

// $bot->forwardMessage($admin_id, $chat_id, $message_id);
// $bot->copyMessage($admin_id, $chat_id, $message_id);





function requestCallbackQuery($bot, $callback_query, $master, $admin) {
    $from = $callback_query['from'];
        $from_id = $from['id'];
    $message = $callback_query['message'];

        if ($message['reply_to_message']) {
            $reply_to_message = $message['reply_to_message'];
            if ($reply_to_message['voice']) {
                $reply_voice = $reply_to_message['voice'];
                $file_id = $reply_voice['file_id'];
            }        
            if ($reply_to_message['text']) {
                $reply_text = $reply_to_message['text'];
            }
        }

        $message_id = $message['message_id'];
        $message_from = $message['from'];
            $message_from_first_name = $message_from['first_name'];
        $chat = $message['chat'];
            $chat_id = $chat['id'];
        $text = $message['text'];        
    $data = $callback_query['data'];
    
        
    if ($data == "client_id")
    {
        $send = "Ваш номер: \r\n\r\n" . $from_id;
        $bot->sendMessage($from_id, $send);

        return;
    }
       
    
   

    /********************************************
    
        ГОЛОСОВАНИЕ

    *********************************************/
    if ($data == "vote_agree" || $data == "vote_against" || $data == "vote_hold")
    {    
        
        if ($data == "vote_agree") $vote = 'agree'; // За
        if ($data == "vote_against") $vote = 'against'; // Против
        if ($data == "vote_hold") $vote = 'hold'; // Воздержался

        $user = User::findOne(['tg_id' => $from_id, 'disabled' => 0]);

        if ( ! $user) {
            $bot->sendMessage($from_id, "Вы не зарегестрированны!");
            return;
        }
        
        if ( ! $text){
            $bot->sendMessage($from_id, "Ошибка, не найден номер голосования!");
            return;
        }
        
        $vote_id = substr($text, 0, strpos($text, "Голосование"));

        $stat = new MailingVoteStat;
        $stat->mailing_vote_id = $vote_id;
        $stat->user_id = $user->id;
        $stat->vote = $vote;
        if ($stat->save()) {            
            $bot->deleteMessage($from_id, $message_id);
            $bot->sendMessage($from_id, "Благодарим за ваше решение, информация отправлена администратору для сбора статистики.  Позднее мы сообщим Вам  результаты голосования.");
        }else {
            $bot->sendMessage($from_id, "Ошибка сохранения результата голосования!");
        }

        return;
    }




    /*******************************************
    
        ВОПРОС ОТ ПОЛЬЗОВАТЕЛЯ (Да, задать)

    ********************************************/
    if ($data == "question_yes")
    {         

        $user = User::findOne(['tg_id' => $from_id, 'disabled' => 0]);

        $send = $from_id . "\r\nСообщение от клиента!\r\n\r\n";

        if ($user) {
            if ($user->role == User::ROLE_MEMBER) {
                if ($user->lastname == "lastname") {
                    $send .= "Участник (упрощённая регистрация)";
                }else {
                    $send .= "Пайщик / Участник";
                }
            }else if ($user->role == User::ROLE_PARTNER) {
                $send .= "Пайщик / Партнёр";
            }else if ($user->role == User::ROLE_PROVIDER) {
                $send .= "Пайщик / Поставщик";
            }

            if ($user->role == "member" && $user->lastname == "lastname") {
                $send .= "\r\n" . $user->firstname . " " . $user->patronymic;
            }else {
                $send .= "\r\n" . $user->lastname . " " . $user->firstname . " " . $user->patronymic;
            }
        }else {
            $send .= "Пользователь не зарегистрирован";
        }

        $bot->deleteMessage($from_id, $message_id);
        if ($reply_voice) {
            $bot->sendVoice($admin, $file_id, $send);
            $bot->sendMessage($from_id, "Сообщение отправлено на обработку администратору, в ближайшее время он Вам ответит!");
        }else if ($reply_text) {
            $bot->sendMessage($admin, $send . "\r\n\r\n" . $reply_text);
            $bot->sendMessage($from_id, "Сообщение отправлено на обработку администратору, в ближайшее время он Вам ответит!");
        }else {
            $bot->sendMessage($from_id, "Можно отправлять только текстовые и голосовые сообщения!");
        }
        
        return;
    }

    
    /**************************
    
        ВОПРОС ОТ ПОЛЬЗОВАТЕЛЯ (Нет, не надо)

    **************************/
    if ($data == "question_no")
    {         
        $bot->deleteMessage($from_id, $message_id);
        $bot->sendMessage($from_id, "Не сразу понял Ваших намерений, извините. Жду дальнейших команд.");

        return;
    }
    

    /**************************
    
        ТЕСТ

    **************************/
    if ($data == "test_edit")
    {    
        $send = "Исправленный текст!!!";
               
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[
                [
                    'text' => 'Пример 1',
                    'callback_data' => 'callback_data'
                ],
                [
                    'text' => 'Пример 2',
                    'url' => 'https://ya.ru'
                ],
            ]]
        ];  

        $bot->editMessageText($from_id, $message_id, $send, null, $InlineKeyboardMarkup);

        return;
    }    
    if ($data == "callback_data")
    {    
        $send = "Тест!!!";
               
        $bot->sendMessage($from_id, $send);

        return;
    }


    /**************************
    
        О НАС (страница 2)

    **************************/
    if ($data == "about_str2")
    {    
        $send = "Выстроенная организация прямого финансирования конечными потребителями, заготовок, переработки и производства отборной продукции, позволяют мелкому , отечественному производителю найти возможность дополнительной реализации и большего получения средств для развития собственного дела, как за счёт дополнительной реализованной продукции, так и за счёт Программ Потребительского общества.
На данный момент реализация заявленных целей осуществляется через программу
Стол заказов «Будь здоров», на сайте Будь-здоров.рус за счёт договоров на прямую поставку продукции от фермеров и частных лиц. Участники (пайщики) через потребительскую программу имеют возможность без привлечения (организации) юридических лиц быть производителями собственных товаров, а также потребителями, заказывать и получать продукты питания, товары народного потребления и услуги, желаемого качества и цены.";
               
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'about_str3'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    
    /**************************
    
        О НАС (страница 3)

    ***************************/
    if ($data == "about_str3")
    {  
        $send = "Кооперативная основа организации позволяет произвести оптимизацию затрат на производство товаров и снизить цены для участников, на прямую от производителя (в сравнении с их аналогами на рынке) .
Усилиями Общества разработан и уже функционирует в тестовом режиме электронный автоматизированный модуль в сети интернет, позволяющий комплексно собирать и обрабатывать поступившие заявки от участников, консолидировать их в заказы и отправлять поставщикам.
Модуль позволяет совершать оперативное информирование, общение с участниками (потребителями и производителями), отслеживать текущее положение дел, проводить сбор мнений (по средствам общего голосования) участников, но, самое основное, он автоматически генерирует весь внутренний документооборот, установленный законодательством РФ, на все необходимые операции.";

        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'about_str4'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    
    /**************************
    
        О НАС (страница 4)

    ***************************/
    if ($data == "about_str4")
    {          
        $send = "Исходя из вышеизложенного, мы можем предложить:
Для участников потребителей
♦ Дополнительный выбор продуктов питания, товаров народного потребления, услуг желаемого качества , исключительно отечественного производства. 
♦ Возможность снижения стоимости товаров и услуг с их аналогами на рынке.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'about_str5'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }

    
    /**************************
    
        О НАС (страница 5)

    ***************************/
    if ($data == "about_str5")
    {          
        $send = "Для участников производителей и партнёров
♦ Дополнительный рынок сбыта производимой продукции.
♦ Лигалализацию своей деятельности (возможность заниматься производством и реализацией продуктов питания, товаров народного потребления и услугами), отсутствие сертфикации и других требуемых необходимостей государстенной регистрации на право производственной деятельности.
♦ Помощь в организации производственной деятельности на местах в виде консультаций по вопросам оптимизации затрат на производство товаров, что повышает общую доходность производства и снижает конечную цену для потребителя.
♦ Для потребительских обществ предоставит возможность использования полного программно-информационного, автоматизированного комплекса в своей деятельности.";
        
        $bot->sendMessage($from_id, $send);

        return;
    }
    

    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 1)

    ************************************************/
    if ($data == "program_reasonable")
    {          
        $send = "Программа “Разумный подход”

В программе «Разумный подход» задействованы два счёта, это лицевой и инвестиционный, счёта.
На лицевой счёт зачисляются средства для приобретения товаров, а на инвестиционный, накопительный счёт, участникам возвращается кооперативная выгода.
Таким образом участники этой программы получают две кооперативные выгоды:
1-я - они приобретают товары дешевле (чем их аналоги на рынке), минуя армию посредников;
2-я - на инвестиционный счёт от всех своих приобретений, получают обратно 7 %  кооперативной выгоды.
Программа настроена автоматически так, что все внесённые в неё предложения (т. е. товары) нацениваются на 17%";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str2'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }
    

    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 2)

    ************************************************/
    if ($data == "program_reasonable_str2")
    {          
        $send = "Вы спросите куда же распределяются остальные 10 %? Разумно! Отвечаем.
Дополнительно 3% зачисляются всем участникам, от общей суммы каждого заказа приглашенных им в эту программу своих знакомых, это производится на постоянной основе, пока приглашенные ими участники будут пользоваться этой программой.
Другими словами, чем больше участников Вы пригласите, тем больше и активнее они будут формировать вместе с Вами, Ваш инвестиционно - накопительный счёт.

Оставшиеся 7% отчисляются участникам, организовавшим  ПУНКТ  ВЫДАЧИ товаров в населённом пункте (На сайте указаны телефоны организаторов пунктов выдачи).
Любая работа отнимает время, а потому  требует за это компенсации.
Средства, скопившиеся на инвестиционном - накопительном счёте участника, последний, может задействовать в одну из предложенных Потребительским обществом, кооперативных программ.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str3'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }



    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 3)

    ************************************************/
    if ($data == "program_reasonable_str3")
    {          
        $send = "Как это выглядит - Например:

 Общим собранием пайщиков, решено, что для общих нужд участников (пайщиков) срочно требуется купить производственное оборудование или организовать собственное производство, скажем  пасеку или маслобойню.  
Не зависимо от выбора, участники - пайщики, решившие задействовать свои скопившиеся инвестиционные средства в это производство становятся инвесторами или другими словами сособственниками приобретённого имущества. 

Мы заинтересованы в том, что бы наши участники были самодостаточными людьми, а значит, они могут рассчитывать,  на более дешёвый мёд, масло или дивиденды от производственного процесса.
По мере развития этого предприятия, объём дивидендов так же будет увеличиваться.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str4'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 4)

    ************************************************/
    if ($data == "program_reasonable_str4")
    {          
        $send = "На должности управленцев или ведущих специалистов этого производства в первую очередь имеют право так же наши пайщики, обладающие необходимыми знаниями и навыками.
        
Раз зашёл пример о пасеке, приведу конкретный пример с мёдом:
1. Закупка мёда у нас производится на Алтае, по цене 200 р. за 1 кг. (в этой стоимости находится сама себестоимость конечного продукта вместе с зарплатой пасечника и процент прибыли на дальнейшее развитие).
2. Издержки на доставку удорожают мёд на 35-40 руб. на 1 кг. И того на выходе стоимость мёда увеличивается до 240р.
3. Добавляем 17% кооперативной надбавки и того 240+17%=280.80р.

Под заказ наши участники получают мёд (куботейнерами) по цене 280р.80к за 1 кг.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str5'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 5)

    ************************************************/
    if ($data == "program_reasonable_str5")
    {          
        $send = "Кто решил взять мёд 3-х л.банку, он выходит по цене 350 за 1 кг.(350х4.5кг в банке=1575)
Сюда входят издержки, сопряжённые с фасовкой и прочим.
4. Из этих 1575 руб., потраченных на приобретение 3-х л./б мёда на инвестиционный счёт участника - покупателя обратно зачисляется 110р.. (7%)
И так, с каждым приобретённым товаром, происходит  на постоянной основе.

Только теперь процентом прибыли который оставался у пасечника, будет распоряжаться круг людей финансировавших данное предприятие.
Эти средства можно вложить в новое дело или расширить уже имеющееся, или забрать свой пай в виде прибыли себе.
Мы предлагаем самый не рисковый вариант к стабильности каждого. 
Созаваемая своими руками падушка безопасности в критических обстоятельствах всегда будет поддерживать участников.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str6'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 6)

    ************************************************/
    if ($data == "program_reasonable_str6")
    {          
        $send = "Риск - 0. Так или иначе нам приходится ходить в магазин и тратить и приобретать продукты за Спасибо!
В лучшем случае можно получить КЭШ БЭК.
Хочечется немного сказать о  КЭШ БЭК. Его сейчас возвращает каждый, кому не лень. Следует признать, что в экономике чудес не бывает.
Сначала возьмут у вас эти средства при оплате а потом часть из них возвращают покупателю обратно, что бы простимулировать дальнейшую покупательскую способность. 
Но при этом прибылью делится ни кто с покупателем не собирается.

Ну вернули 1 или 2 тысячи руб. обратно, вы как то от этого разбоготели или у вас появилась перспектива?
Самое сложное в любом деле это знания, навыки и контроль. Много ли у нас компетентных людей в подобных вопросах? Думается, что не мало.)) 
Ну так они все при деле, а что делать остальным у кого нет этих навыков или возраст не позволяет самостоятельно вести бизнес?";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'program_reasonable_str7'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        ПРОГРАММА "разумный подход"  (страница 7)

    ************************************************/
    if ($data == "program_reasonable_str7")
    {          
        $send = "Потребительское общество “Общее дело” берёт на себя ответственность объеденить людей,  из числа участников найти специалистов под конкретные задачи, производить своевременный контроль необходимиых процессов, предоставлять отчёты всем участникам, сохранять и приумножать средства инвесторов  а так же общие фонды в целом.

В нашей программе “Разумный подход”,  инвестором может стать любой разумный человек от 14 и до 140 лет.

Данное мероприятие участников ни к чему не обязывает, в любой момент каждый может выйти из участия за исключением того, что у организации остаётся обязанность по договору инвестирования, выплачивать дивиденды вкладчику.
Накопленные инвестиционные средства и вложенные доли в предприятия, передаются по заявлениям по наследству или в дар они  не пропадают бесследно, после вашего выхода из Общества.
";
        
        $bot->sendMessage($from_id, $send);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 2)

    ************************************************/
    if ($data == "cooperation_str2")
    {          
        $send = "Итак, в этой книге Вы узнаете:

♦ Что такое потребительский кооператив;

♦ Структуру управления потребительским кооперативом;

♦ Всё о пайщиках;

♦ Всё о паевом фонде потребительского кооператива;

♦ Многие вопросы налогового характера;

♦ Почему потребительские общества – «инвестиционный рай» для ведения бизнеса;

И многие другие вопросы и возможности потребкооперации.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str3'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 3)

    ************************************************/
    if ($data == "cooperation_str3")
    {          
        $send = "Кому адресована эта книга?

Пожалуй, она будет полезна всем. И тем, кто впервые столкнулся с этим явлением, и тем, кто давно работает на базе потребкооперации.

Ведь далеко не редкость, когда у зарегистрированного юридического лица столько ошибок в учредительных документах, бухгалтерской отчетности, да и в самой схеме работы, что деятельность такого кооператива, по сути, является незаконной.
Именно поэтому мы и приглашаем всех в наш Учебный Центр. Лучше с самого начала правильно организовать дело, чем наделать ошибок, и впоследствии, перерегистрировать юридическое лицо, параллельно отбиваясь от «нападок» налоговой службы, пайщиков, кредиторов…
Мы понимаем, что для многих эта книга – лишь первый шаг на пути к знакомству с потребительскими обществами. Вам наверняка захочется узнать больше, и получить ответы на все возникшие вопросы.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str4'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 4)

    ************************************************/
    if ($data == "cooperation_str4")
    {          
        $send = "Мы всегда будем рады Вам помочь, все вопросы и предложения Вы можете задать через личный кабинет на нашем сайте.
А так же с вопросами и предложениями можете обращаться на адрес: p-o-n@list.ru

Здесь Вы найдете весь комплекс инструментов для обучения: от бесплатных статей до эффективных пособий и комментариев профессиональных экспертов.

Оглавление:
    Глава 1. Понятие потребительского общества
    Глава 2. Пайщики: кто они?
    Глава 3. Все о паевом фонде
    Глава 4. Управление потребительским кооперативом
    Глава 5. Потребительский кооператив и налогообложение
    Глава 6. Потребительский кооператив – «инвестиционный рай».
    Глава 7. А что дальше?";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str5'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 5)

    ************************************************/
    if ($data == "cooperation_str5")
    {          
        $send = "Глава 1. Понятие потребительского общества
        
Сама схема работы потребительского кооператива достаточно проста: силами пайщиков внутри кооператива ведется внутрихозяйственная деятельность.
        
Работа строится на участии пайщиков. Прежде всего, за счет взносов (паевых, вступительных, членских), но так же и благодаря непосредственному их участию в совершенно разных вариантах. Это может быть организация закупок, ведение бухучета общества – все, что может быть полезным.

Поскольку деятельность потребительского общества основана на пайщиках, то общество, в свою очередь, безвозмездно оказывает услуги пайщикам и членам их семей. (конечно, если пайщики – физические лица).";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str6'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 6)

    ************************************************/
    if ($data == "cooperation_str6")
    {          
        $send = "Некоммерческая деятельность. Пайщики потребительского общества делают взносы, а не производят оплату. И это принципиально, поскольку согласно гражданскому законодательству кооперативы, потребительские общества относятся к некоммерческим юридическим лицам, и не должны получать прибыль в качестве основной своей деятельности.
Взносы могут быть не только в денежном эквиваленте, но и в виде имущества, и даже неимущественных благ (которые, правда, имеют денежную оценку).
Важный момент: все взносы идут только на уставные цели потребительского общества и на его дальнейшее развитие.
Однако некоммерческий характер деятельности вовсе не означает, что с помощью потребкооперации нельзя заработать! Законодатель предусмотрел здесь довольно много законных вариантов: это и возможность неограниченной материальной поддержки пайщиков, и вариант кредитования, и механизм возврата паевого взноса – все зависит от того, как Вам удобнее будет заложить алгоритм работы!";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str7'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 7)

    ************************************************/
    if ($data == "cooperation_str7")
    {          
        $send = "Чем же могут заниматься потребительские общества?
Все, что приобретается и осуществляется силами пайщиков – товары, продукты, услуги, распределяется между ними же. Это так называемое внутренне потребление. И это тоже важно: именно такая внутрихозяйственная деятельность не облагается налогами!
По Закону «О потребительской кооперации…» основные задачи потребительского общества:
    ≈ торговля для обеспечения товарами членов общества;
    ≈ закупка сельхозпродукции и сырья;
    ≈ производство пищевых продуктов и непродовольственных товаров;
    ≈ оказание членам потребительского общества производственных и бытовых услуг;";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str8'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 8)

    ************************************************/
    if ($data == "cooperation_str8")
    {          
        $send = "Для специальных видов кооперативов – строительных, кредитных и пр., - предусмотрено специальное законодательство, и здесь мы их не рассматриваем.
«Так как же сопоставить некоммерческую деятельность и бизнес?» - наверняка удивляетесь Вы. На самом деле законом предусмотрена чуть ли не 1000 и 1 возможность для получения Вашей личной прибыли. В этой книге мы рассмотрим некоторые из них.

Глава 2. Пайщики: кто они?

Закон устанавливает следующий минимум для учреждения потребительского кооператива:
• 5 физических лиц или
• 3 юридических лица";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str9'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 9)

    ************************************************/
    if ($data == "cooperation_str9")
    {          
        $send = "Физические лица должны быть не младше 16 лет. При этом, например, гражданство не имеет абсолютно никакого значения. То есть участником может быть как гражданин РФ, так и иностранец, так и лицо без гражданства. Для юридических лиц тоже нет каких-то специальных ограничений.

Для потребительского кооператива совершенно не важно, кто именно является участником. Будь то скромный пенсионер, индивидуальный предприниматель, или, например, холдинг с миллионными оборотами.

В кооперативе все, кто внес паевой и вступительные взносы, имеют 1 голос. То есть действует схема 1 участник=1 голос вне зависимости от прочих условий. При этом привилегий не имеют даже учредители. Вне зависимости от времени вступления все участники равны в своих правах.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str10'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 10)

    ************************************************/
    if ($data == "cooperation_str10")
    {          
        $send = "Для лучшего понимания внутренней работы потребительского общества условно всех пайщиков можно разделить примерно на следующие категории:
♦ Пайщики-инвесторы. Они становятся участниками с целью получить определенные дивиденды. Обычно с ними заключаются специальные договоры;
♦ Пайщики-поставщики. Приходят в потребительское общество для реализации своей продукции;
♦ Пайщики-потребители. Их основной интерес – получить товары, услуги, продукцию по более низкой стоимости, или на более выгодных условиях.
♦ Пайщики-сотрудники. Это условное название. Обычно выгоднее не заключать с такими пайщиками трудовые договоры, дабы избежать отчислений в социальные фонды. Эти участники в основном помогают именно в административном плане – секретари, бухгалтеры, и пр.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str11'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 11)

    ************************************************/
    if ($data == "cooperation_str11")
    {          
        $send = "Конечно, такое разделение  несколько условно, и один пайщик может «попадать» сразу в несколько категорий.
        
Права и обязанности пайщиков в основном прописаны в законе, но конкретизируются они уже в уставных документах.

Глава 3. Все о паевом фонде

Паевой фонд – это основной «источник» для накопления имущества потребительского общества.

Но что для нас более интересно, что именно благодаря паевому фонду во многом и организуется “некоммерческий бизнес”. Но об этом чуть позже.

Итак, паевой фонд – это фонд, состоящий из паевых взносов, вносимых пайщиками при создании потребительского общества или вступлении в него.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str12'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 12)

    ************************************************/
    if ($data == "cooperation_str12")
    {          
        $send = "При этом закон не устанавливает никаких требований ни к размеру самого фонда, ни к имуществу, вносимому в него.

Получается, что фонд может равняться даже 1 рублю. Хотя мы искренне не советуем экономить на нем – рано или поздно Вы поймете, каким образом фонд можно использовать, и тогда придется ставить вопрос перед общим собранием об изменении его размера.

Что и как можно внести в паевой фонд?

Это может быть практически любое имущество – движимые и недвижимые вещи, деньги, транспортные средства и даже нематериальные активы. Но, правда, только те, которые имеют денежную оценку. Например, авторское право неотчуждаемо, и не имеет денежного эквивалента. А вот уже права публикации, авторские гонорары можно вносить в паевой фонд потребительского общества.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str13'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 13)

    ************************************************/
    if ($data == "cooperation_str13")
    {          
        $send = "По какой стоимости будет вноситься имущество?

Строго установленных правил нет. Обычно стоимость «определяет» совет потребительского общества или специально созданная им комиссия.
Однако есть некоторые довольно редкие исключения, которые требуют, чтобы оценка вносимого в паевой фонд имущества проводилось независимым оценщиком.

Какими документами сопровождается внесение пая?
    ♦ Заявление пайщика;
    ♦ Документ об оценке (заключение независимого оценщика, решение совета или оценочной комиссии);
    ♦ Подписывается договор о внесении паевого взноса;
    ♦ Акт приема-передачи;

Акт приема передачи можно оформить как отдельным документом, так и предусмотреть этот пункт в тексте договора, что он выполняет функции акта приема-передачи.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str14'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 14)

    ************************************************/
    if ($data == "cooperation_str14")
    {          
        $send = "♦ Если вносится недвижимость, то требуется еще пройти регистрацию перехода права собственности и получить свидетельство о государственной регистрации.

Что еще можно сказать о паевых взносах? То, что они возвратные, и это принципиально важно для нас. Для пайщиков это, как минимум, означает гарантии, что его не обманут. Ведь с момента оформления всех документов собственником становится потребительское общество.
        
Но есть и более интересные возможности паевого взноса!
       
Во-первых, имущество, внесенное в паевой фонд, не оседает там «мертвым грузом». Переданные Вами дома, машины, картины и компьютеры после оформления документов общество может вернуть Вам обратно в безвозмездное пользование. То есть, Вы, как жили в доме, так и живете, как пользовались своим ноутбуком, так и продолжаете им пользоваться.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str15'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 15)

    ************************************************/
    if ($data == "cooperation_str15")
    {          
        $send = "Просто чисто технически поменяется собственник. Но имущество у Вас уже не смогут забрать ни кредиторы, ни фискальные органы. Вы лично уже не должны будете уплачивать налог на имущество, и пр.
        
Во-вторых, внеся в паевой фонд потребительского общества свое имущество, Вы можете его не только «спрятать», и даже приумножить! Теоретики потребкооперации называют эту возможность «стадией сохранения и приумножения» собственности.
        
Здесь мы сделаем небольшое отступление. Ведь многие, прочитав о возможности передачи недвижимости в паевой фонд, наверняка подумали: «Ни за что!». И такая реакция вполне понятна – уж слишком много раз наших граждан обманывало и государство и бессовестные коммерсанты.    
Однако в данном случае и законодательно, и практикой, предусмотрен весьма мощный, и надежный механизм защиты пайщика.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str16'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 16)

    ************************************************/
    if ($data == "cooperation_str16")
    {          
        $send = "При вступлении в потребительское общество, и внесении имущества в паевой фонд, между обществом и пайщиком заключаются соответствующие договоры.

Их условия согласовываются непосредственно сторонами. То есть, Вы можете прописать практически любые условия, которые обезопасят Вас и переданное Вами имущество.

Соответственно, если Вы хотите, чтобы недвижимость Вам передали «обратно», надо, как минимум, предусмотреть следующие моменты:
        
    Что потребительское общество обязуется передать Вам недвижимость в безвозмездное пользование;        
    Что недвижимость передается бессрочно;        
    Что если что-то случится с Вами (все мы люди!), то условия договора распространяются на Вашу жену/детей/родственников.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str17'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 17)

    ************************************************/
    if ($data == "cooperation_str17")
    {          
        $send = "Это рекомендуемый минимум, а еще лучше предусмотреть моменты невозможности передачи потребительским обществом права собственности другому лицу, о невозможности расторжения заключенных договоров, или весомые штрафные санкции за это, и пр.
Здесь главное то, что Вы сами можете предлагать те условия, которые гарантируют Вам, по сути, спокойствие, что Вас не оставят на улице.
        
Далее. После заключения договора о передаче имущества в паевой фонд, перерегистрации перехода права собственности, и заключении договора о передаче имущества Вам в пользование, у Вас возникает право на владение.        
Гражданское законодательство очень серьезно подходит к вопросу владельческой защиты, и дает хорошие гарантии. Прежде всего, ст. 305 Гражданского Кодекса говорит о том, что все права и возможности, которые даны собственнику для защиты своего права, распространяются и на владельца.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str18'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 18)

    ************************************************/
    if ($data == "cooperation_str18")
    {          
        $send = "А далее - что законный владелец может защищать свое право даже от собственника!!! Так что, если грамотно составить договор о передаче имущества, то Вам не страшны ни собственник, ни приставы.

Про сам договор безвозмездного пользования (ссуды) в Кодексе так же есть целая 36 глава. Там так же подробно и основательно прописаны права, обязанности и гарантии сторон.

Есть еще один «страховочный» момент: паевой взнос не только является возвратным, он еще и передается по наследству. Это касается не только недвижимого, а вообще, любого имущества, которое Вы передали в качестве паевого взноса.
Соответственно, Вы можете не беспокоиться не только за себя, но и за своих детей, самых близких людей.
А теперь, давайте вернемся к теме – «стадии сохранения и приумножения» собственности.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str19'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 19)

    ************************************************/
    if ($data == "cooperation_str19")
    {          
        $send = "Каким образом гарантируется сохранность?
≈ путём создания так называемых неделимых фондов, которыми управляет потребительское общество только по решению общего собрания пайщиков, и которые не распределяются между членами, учредителями и работниками по найму;
≈ собственность, внесённая в виде паевого взноса, становится собственностью потребительского общества, и не может быть отчуждена без решения общего собрания;
≈ имущество потребительского общества имеет высшую степень защиты от внешнего вмешательства в его внутрихозяйственную деятельность, в т.ч. государственных органов и органов местного самоуправления;
≈ На имущество общества не могут быть обращены требования кредиторов его участников. В соответствии с п. 2 ст. 25 Закона «О потребительской кооперации…», общество не отвечает по обязательствам пайщиков;";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str20'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 20)

    ************************************************/
    if ($data == "cooperation_str20")
    {          
        $send = "≈ имущество потребительского общества не распределяется по долям, вкладам, между пайщиками и работниками по трудовому договору (п.2 ст.21 Закона о потребительской кооперации);
≈ это имущество может быть передано в доверительное управление с целью увеличения активов (по решению общего собрания);
≈ Паевой взнос, и права, которые в связи с его внесением возникают, в случае смерти пайщика, передаются по наследству его наследникам. Здесь нет никаких оговорок и исключений.
≈ В случае ликвидации потребительского общества, его паевой фонд не распределяется между руководителями. Он может быть передан другому потребительскому обществу. Что это означает для участников? По факту – свободу выбора: организовать свое потребительское общество, и получить «кусочек» паевого фонда, или стать пайщиком другого ПО, и «перевести» свой паевой взнос туда.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str21'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 21)

    ************************************************/
    if ($data == "cooperation_str21")
    {          
        $send = "Таким образом, у пайщиков есть достаточно гарантий, которые дают уверенность в том, что потребительское общество – не очередная «серая схема». Но давайте снова вернемся к главному вопросу: каким образом с помощью ПО можно увеличить свой доход?

Как пайщикам получать доход, если потребительское общество не занимается коммерческой деятельностью? Здесь есть как минимум еще один очень привлекательный момент.

Например, Вы вносите в виде пая, например, мебель для офиса потребительского общества, или оргтехнику – имущество, которое обеспечивает его нормальную работу. Взамен у Вас возникает право на возврат его стоимости. А возврат может быть в форме любых товаров, услуг, и даже в денежном эквиваленте!";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str22'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 22)

    ************************************************/
    if ($data == "cooperation_str22")
    {          
        $send = "Получается, что Вы вносите имущество, продолжаете им пользоваться, да еще и получаете за это деньги (или товары, или услуги – как Вам удобнее)! При этом стоимость пая, и, соответственно, размер его «возврата», прописывается тот, который Вам нужен. Но во всём нужно чувство меры что бы это не вызывало подозрений у фискальных органов.
        
Предположим, что члены нашего потребительского общества – большие ценители живописи. Но все же ясно, что  картина, нарисованная одним из наших пайщиков,  не может стоить 1 млн. $, а вот 1 млн. руб. – уже ее более правдоподобная стоимость, ведь мы же истинные ценители живописи!

Глава 5. Управление потребительским кооперативом

Потребительская кооперация – совершенно уникальное явление. Это подчеркивает даже сам законодатель.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str23'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 23)

    ************************************************/
    if ($data == "cooperation_str23")
    {          
        $send = "Например, в законе «О потребительской кооперации…» одним из основных принципов создания и деятельности потребительского общества прямо предусмотрена демократичность управления. Что входит в этот принцип?

Например, уже названная система распределения голосов (1 пайщик=1голос), свободное участие пайщика в выборных органах общества, подотчетность органов общему собранию пайщиков и, конечно же, сама система этих органов.
Здесь заложен принцип «разделения властей». Можно четко выделить законодательные (представительные), исполнительные и «судебные» (в нашем случае – контрольные) органы.

Именно этот баланс гарантирует соблюдение уставных документов общества, прав пайщиков, не допустить самовольства управляющих лиц. А в совокупности все это гарантирует нормальную работу общества и выполнение его уставных целей.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str24'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 24)

    ************************************************/
    if ($data == "cooperation_str24")
    {          
        $send = "Какие же органы управления необходимы в потребительском обществе?

Управление осуществляют общее собрание потребительского общества, совет и правление.
        
Высший орган – общее собрание. В период между общими собраниями пайщиков управление осуществляет совет. Соответственно, эти два органа – представительные.

Исполнительным же органом в обществе является правление потребительского общества.
Контрольные функции за финансовой и хозяйственной деятельностью возложены на ревизионную комиссию.

Законом прямо предусмотрены вопросы, относящиеся к исключительной компетенции общего собрания и совета. Устав общества может расширять их перечень, но ни в ком случае – не сокращать.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str25'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 25)

    ************************************************/
    if ($data == "cooperation_str25")
    {          
        $send = "Остальные вопросы, не входящие в исключительную компетенцию представительных органов, могут быть переданы на решение правления потребительского общества.

Ревизионная комиссия же контролирует соблюдение устава, финансовую и хозяйственную деятельность потребительского общества.

Вопросы, касающиеся управления потребительским обществом, и его органов в частности, настолько многообразны, и они так важны, достойны отдельной книги.

Здесь много тонкостей – это и способ избрания, и соответствующее оформление документов, и минимально необходимое количество участников для кворума. Много нюансов в отношении числа голосов при принятии решений.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str26'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 26)

    ************************************************/
    if ($data == "cooperation_str26")
    {          
        $send = "Но, пожалуй, самое важное – как организовать управление в потребительском обществе так, чтобы была возможность его контролировать.
Единственно правильной схемы нет, все зависит от конкретных обстоятельств, поэтому здесь необходимы индивидуальные консультации, которые Вы всегда можете получить у нас в Центре.
        
Глава 6. Потребительское общество и налогообложение
        
Очень часто, чтобы как-то уменьшить бремя налогов, предприниматели придумывают различные схемы. Например, открывается не одно юридическое лицо, а сразу несколько – с разными системами налогообложения. И дальше, в зависимости от условий сделок, контрагента и пр., предприниматель начинает «жонглировать» - то проводка идет через фирму с общей системой налогообложения, то через «вменёнку», «упрощёнку», патент…";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str27'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 27)

    ************************************************/
    if ($data == "cooperation_str27")
    {          
        $send = "И это только если говорить о более-менее законных способах.

А ведь практически любой бизнес, в котором вашим «клиентом» будет гражданин-потребитель, индивидуальный предприниматель, или юридическое лицо, работающие через УСН, ЕНВД и патент, можно выгодно для всех сторон организовать на платформе потребительского кооператива.

Почему именно эти лица? Потому что если они получают товар или услугу по выгодной стоимости, получают «потребительскую выгоду». А значит, Ваша деятельность признается социально значимой, и вы получаете очень интересные налоговые послабления.

Почему же учреждение потребительского общества интересно с точки зрения налогообложения?";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str28'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 28)

    ************************************************/
    if ($data == "cooperation_str28")
    {          
        $send = "Дело в том, что по законодательству некоммерческие организации не платят налог на имущество, если его остаточная стоимость менее 100 млн. рублей.
Кроме того, потребительские общества не платят налог на доход – ведь прибыли у них нет! Давайте на небольших суммах посчитаем, сколько «обходятся» налоги предпринимателям.
Предположим, прибыль (разница между себестоимостью и ценой реализации) у нас составила 20 000 рублей. Но в любом деле есть свои затраты и издержки – аренда площадей, зарплата работникам, транспортные расходы, и т.д. Давайте округлим, и представим, что сумма этих затрат равняется 5 000 рублей.

Таким образом, наша прибыль до налогообложения составляет
    20 000 – 5 000 = 15 000 рублей
Теперь посчитаем сумму налога. Ставка на данный момент равняется 20% (!).
    15 000 * 20% = 3 000
И посчитаем «чистую прибыль».
    15 000 – 3 000 = 12 000";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str29'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 29)

    ************************************************/
    if ($data == "cooperation_str29")
    {          
        $send = "Так что бизнесмен «теряет» почти 40% от первоначальной прибыли!
А вот в потребительских обществах нет ни налога на прибыль, ни налога на имущество. Получается, что налогооблагаемой базы здесь нет вообще. Это, во-первых, дает возможность существенно снизить стоимость товаров и услуг, необходимых пайщикам, а во-вторых, это позволяет оставить больше денег внутри самого общества.
Подразумевается, что эти суммы будут потрачены на развитие и нужды самого потребительского общества, но есть и абсолютно законные способы «вывести» эти деньги.
И все же, при регистрации потребительского общества, как юридического лица, возникнет вопрос о выборе системы налогообложения. Рекомендуем сразу написать заявление о переходе на УСН. На упрощенке размер налога составляет 6%. Но платить его нужно, только если за налоговый период потребительское общество занималось какой-то коммерческой деятельностью и получило доход.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str30'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 30)

    ************************************************/
    if ($data == "cooperation_str30")
    {          
        $send = "Если же деятельность была исключительно некоммерческой, Вы будете сдавать «нулевую» отчетность.
Многие удивляются, почему государство добровольно оставляет возможность не платить налоги, и боятся, проверок и претензий от налоговых органов.
Бояться этого не стоит. Сам законодатель в ст. 1 Закона «О потребительской кооперации…» говорит: «… Закон гарантирует потребительским обществам и их союзам с учетом их социальной значимости, а также гражданам и юридическим лицам, создающим эти потребительские общества и их союзы, государственную поддержку».
Государству выгодно, чтобы социально важные вопросы граждане и юридические лица решали самостоятельно, своими силами. Именно поэтому потребительским обществам даются такие значительные послабления и льготы.
Только, разумеется, надо очень внимательно относиться к оформлению документов и ведению бухгалтерии.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str31'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 31)

    ************************************************/
    if ($data == "cooperation_str31")
    {          
        $send = "Глава 7: Почему потребительские общества – инвестиционный рай?

Еще раз повторимся – потребительские кооперативы – почти неизвестная широкой аудитории категория. Поэтому давайте подведем итог, и кратко выберем самые интересные возможности потребительских обществ:
♦ Государство не имеет права вмешиваться во внутреннюю, некоммерческую сферу деятельности потребительских кооперативов.
♦ При переводе бизнеса, или какой-то его части, в сферу потребкооперации, можно существенно снизить налоговую нагрузку. Потребительские общества освобождены от уплаты налога на имущества, если его остаточная стоимость составляет менее 100 миллионов рублей. Кроме того, не платится НДС, 18% и налог на прибыль – 20%.
♦ Если в потребительском обществе нет сотрудников, работающих по трудовому договору, то нет необходимости делать отчисления в социальные фонды.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str32'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 32)

    ************************************************/
    if ($data == "cooperation_str32")
    {          
        $send = "♦ Механизм паевых взносов дает практически неисчерпаемые возможности пайщикам:

Эти взносы – возвратные. Так можно получать обратно имущество или денежные средства, которые не облагаются никакими налогами.
Имущество, внесенное в паевой фонд, становится собственностью потребительского общества. С этого момента на него не могут быть обращены взыскания по личным долгам пайщика. Так можно «спрятать» имущество от кредиторов и рейдерских захватов.
Имущество, внесенное в паевой фонд, может быть передано обратно пайщику в бессрочное пользование. То есть собственник меняется чисто технически, а Вы можете и дальше владеть и пользоваться своим имуществом.

♦ По общему правилу потребительские общества не занимаются коммерческой деятельностью, поэтому им не обязательно пользоваться кассовыми аппаратами.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str33'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 33)

    ************************************************/
    if ($data == "cooperation_str33")
    {          
        $send = "♦ Потребление товаров и услуг происходит внутри общества, не предлагается лицам, не входящим в число пайщиков. Поэтому потребительским обществам разрешено заниматься некоторыми видами деятельности без обязательного лицензирования. В том числе, лизингом и кредитованием своих пайщиков.
♦ Закон прямо предусматривает, что общество может выделять своим участникам материальную помощь. В любых суммах, хоть каждый день. Чтобы все было законно, нужно только уплатить 13% НДФЛ. Хотя, если Вы обратитесь к нам, мы подскажем, как снизить этот процент более чем в 2 раза!
♦ Можно вносить в паевой фонд имущество по «интересной» для Вас цене, и реализовать потом механизм возврата стоимости паевого взноса.
♦ Поскольку участие в потребительском кооперативе не обязательно должно носить характер трудовых отношений, можно забыть про обязательные отчисления в пенсионные и социальные фонды.";
        
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Читать далее',
                'callback_data' => 'cooperation_str34'
            ]]]
        ];  

        $bot->sendMessage($from_id, $send, null, $InlineKeyboardMarkup);

        return;
    }


    /************************************************
    
        КООПЕРАЦИЯ (страница 34)

    ************************************************/
    if ($data == "cooperation_str34")
    {          
        $send = "♦ Законодательство предусматривает возможность сочетать коммерческую и некоммерческую деятельность в рамках одного потребительского общества.

Итого, как минимум 10 «вкусных» возможностей для инвестирования своих средств и прибыльного ведения бизнеса!

И, конечно же, есть много других инструментов, о которых Вы узнаете в нашем Учебном Центре.

А что дальше?

А дальше Вы наверняка захотите узнать больше! Ведь при современной нестабильной экономике возможность вести дело законно, при этом еще и законно оставлять у себя больше денег – это настоящий «инвестиционный рай»!";
        
        $bot->sendMessage($from_id, $send);

        return;
    }



}
