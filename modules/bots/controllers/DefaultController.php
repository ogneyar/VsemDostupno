<?php

namespace app\modules\bots\controllers;

use Yii;
use DateTime;
use yii\web\Controller;
use app\modules\bots\api\Bot;
use app\models\User;
use app\models\Forgot;
use app\models\Email;
use app\models\Account;



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
                    requestProcessing($bot);
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


function requestProcessing($bot) {
    $data = $bot->data;

    if (isset($data['message'])) {
        requestMessage($bot, $data['message']);
    }else if (isset($data['callback_query'])) {
        requestCallbackQuery($bot, $data['callback_query']);
    }    
}


function requestMessage($bot, $message) {
    $from = $message['from'];
        $first_name = $from['first_name'];
    $chat = $message['chat'];
        $chat_id = $chat['id'];
    $text = $message['text'];


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


    /********************
    
           ПОМОЩЬ

    *********************/
    if ($text == "Помощь" || $text == "/help")
    {
        $send = "Команда 'Помощь' в разработке";
        $bot->sendMessage($chat_id, $send);

        return;
    }
    

    /***********************
    
           ИНФОРМАЦИЯ

    ************************/
    if ($text == "Информация" || $text == "/info")
    {
        $send = "Тут Олег не придумал текст.";
    
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
        
        

        $send = "*Доброго времени суток, ".$user->firstname." ".$user->patronymic."!!!*\r\n";

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

        $send .= "Лицевой счёт:                     ".formatPrice($face->total)."\r\n";
        $send .= "Инвестиционный счёт:     ".formatPrice($invest->total)." *Накопительный счёт не задействован.*";

        if ($user->role == User::ROLE_PARTNER) {
            $send .= "Партнёрский счёт:             ".formatPrice($partner->total)."\r\n";
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
                    [ 'text' => 'Программы' ],
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
    if ($text == "Программы" || $text == "/programs")
    {
        
        $send = "--------------------------------------";

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
        $send = "Тут тоже Олег не придумал текст.";
    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Программы' ],
                    [ 'text' => 'Назад' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);

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
        $send = "--------------------------------------";

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
        $send = "--------------------------------------";

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
         
        Производители отечественных (местных) товаров и услуг, предлагают качественную продукцию участникам Общества по доступным ценам.
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
    

}


function formatPrice($price) {
    if (! $price || $price == 0) return "00 руб. 00";
    $floor_price = floor($price);
    $response = $floor_price . " руб. " . ($price - $floor_price);
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



function requestCallbackQuery($bot, $callback_query) {
    $from = $callback_query['from'];
        $from_id = $from['id'];
    $message = $callback_query['message'];
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


}
