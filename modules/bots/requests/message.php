<?php

use Yii;
use DateTime;
use app\models\User;
use app\models\Forgot;
use app\models\Email;
use app\models\Account;
use app\models\TgCommunication;

require __DIR__ . '/../utils/formatPrice.php';



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
                ],
                [
                    [ 'text' => 'Задать вопрос админу' ],
                ],
            ],
            'resize_keyboard' => true,
            // 'one_time_keyboard' => true,
        ];

        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);

        return;
    }


    /***********************
    
           СПЕЦИАЛИСТЫ

    ************************/
    if ($text == "Специалисты" || $text == "/specialists")
    {
        $send = "Выберите";    
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Задать вопрос админу' ],
                ],
            ],
            'resize_keyboard' => true
        ];
        $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);


        $send = "проффесиональное направление.";
    
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => "Юриспруденция",
                        'callback_data' => 'specialists_jurisprudence'
                    ],
                ],
                [
                    [
                        'text' => "Оздоровление",
                        'callback_data' => 'specialists_recovery'
                    ],
                ],
                [
                    [
                        'text' => "Эзотерика",
                        'callback_data' => 'specialists_esotericism'
                    ],
                ]
            ]
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
    
        ЕСЛИ ПРИСЛАЛИ ОТВЕТНОЕ СООБЩЕНИЕ (reply)

    *******************************************/
	if ($reply_to_message && $chat_id == $admin) {
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
        return;
    }
        


    /******************************************
    
        ЕСЛИ ПРИСЛАЛИ НЕИЗВЕСТНОЕ СООБЩЕНИЕ

    *******************************************/
    $tgCom = TgCommunication::findOne(['chat_id' => $chat_id]);

    if ($tgCom) { // если есть запись, отправляем переписку
        
        $user = User::findOne(['tg_id' => $from_id, 'disabled' => 0]);

        if ( ! $user || $user->lastname == "lastname") {
            $send = "Не зарегистрированный пользователь". "\r\n\r\n" . $text;
        }else {
            // $send = "Сообщение от пользователя №" . $chat_id . "\r\n\r\n" . $text;
            $send = "Сообщение от клиента" . "\r\n\r\n" . $text;                  
        }     
                     
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Ответить',
                        'callback_data' => 'otvetit_' . $chat_id
                    ],
                ],
            ]
        ];

        $bot->sendMessage($tgCom->to_chat_id, $send, null, $InlineKeyboardMarkup);
        // $bot->sendMessage($tgCom->to_chat_id, $send);

        // $send = "Сообщение отправлено пользователю №" . $tgCom->to_chat_id;
        // $send = "Ваше сообщение отправлено, при наличии времени специалист с вами сразу свяжется";
        $send = "Ваше сообщение отправлено";
        $bot->sendMessage($chat_id, $send);
            $tgCom->delete();

        return;
    }

    
    if ($chat_id != $admin) {

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
		
        return;
		
	}else {        
        $bot->sendMessage($chat_id, "Ваше сообщение НЕ БУДЕТ отправлено администратору!\r\n\r\nВы и есть администратор!!!");
		
        return;
    }


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


