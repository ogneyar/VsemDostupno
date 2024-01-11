<?php

use Yii;
use DateTime;
use app\models\User;
use app\models\Forgot;
use app\models\Email;
use app\models\Fund;
use app\models\Account;
use app\models\TgCommunication;
use app\models\CartTg;
use app\models\Category;
use app\models\CategoryHasProduct;
use app\models\Product;
use app\models\ProductFeature;
use app\models\ProductPrice;
use app\models\Provider;
use app\models\ProviderHasProduct;
use app\modules\purchase\models\PurchaseProduct;

require_once __DIR__ . '/../utils/formatPrice.php';
require_once __DIR__ . '/../utils/getBalance.php';
require_once __DIR__ . '/../utils/getBalanceByNumber.php';
require_once __DIR__ . '/../utils/editPricePurchase.php';
require_once __DIR__ . '/../utils/putInTheBasket.php';
require_once __DIR__ . '/../utils/cart/getCart.php';
require_once __DIR__ . '/../utils/cart/clearCart.php';
require_once __DIR__ . '/../utils/continueSelection.php';
require_once __DIR__ . '/../utils/homeDelivery.php';
// require_once __DIR__ . '/../utils/getPurchasesOld.php';
require_once __DIR__ . '/../utils/getMainPurchases.php';
require_once __DIR__ . '/../utils/account/getPay.php';
require_once __DIR__ . '/../utils/account/getRole.php';



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

    $user = User::findOne(['tg_id' => $chat_id]);


    /*******************
    
        ГЛАВНОЕ МЕНЮ

    ********************/
    if ($text == "/start" || $text == "Старт" || $text == "/menu" || $text == "Главное меню" || $text == "Назад" ||  $text == "🌟Главное меню" || $text == "⭐️Главное меню⭐️")
    {    

        $send = "В голубом кружочке с низу, в меню, Вы найдёте ссылки на всю необходимую информацию";
               
        $keyboard = [
            [
                [ 'text' => 'Приветствие' ],
                [ 'text' => 'О нас' ]
            ],
            [
                [ 'text' => 'Услуги' ],
            ],
            [
                [ 'text' => 'Новичкам' ],
            ],
            [
                [ 'text' => 'Информация' ],
            ],
            [
                [ 'text' => 'Регистрация' ],
            ],
            [
                [ 'text' => 'Помощь' ],
            ],
        ];
        
        // if ($chat_id == $master) array_push($keyboard, [ [ 'text' => 'Тест' ] ]);

        if ($user && !($user->role == User::ROLE_MEMBER && $user->lastname == "lastname")) {
            array_push($keyboard, [ [ 'text' => 'Моя ссылка' ] ]);
        }

        // if ($user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN || $chat_id == $admin || $chat_id == $master) 
        // {
        //     array_push($keyboard, [ [ 'text' => 'Даты закупок' ] ]);
        // }
        // else if ($user->role != User::ROLE_PROVIDER)
        // {
        //     array_push($keyboard, [ [ 'text' => 'Закупки' ] ]);
        // }

        // $cart = CartTg::findOne(['tg_id' => $chat_id]);
        // if ($cart) {
        //     array_push($keyboard, [ [ 'text' => 'В корзине товар' ] ]);
        // }

        $ReplyKeyboardMarkup = [
            'keyboard' => $keyboard,
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
            if ($user) {
                $bot->sendMessage($chat_id, "Вы уже зарегестрированны!");
                return;
            }

            // $send = "Здравствуй " . $first_name . "!\r\n\r\n";
            // $send .= "Добро пожаловать, это регистрация на сайте Будь-Здоров.рус.\r\n";
            // $send .= "В боте Вы уже зарегестрированны. Для продолжения регистрации нажмите на кнопку ниже (прикреплена к этому сообщению).";

            $send = "Мы приветствуем Вас!!!\r\n\r\n";
            $send .= "Добро пожаловать на страницу регистрации участников Клуба Будь здоров!\r\n";
            $send .= "В телеграмм канале регистрация прошла успешно. Для управления функциями своего личного кабинета,";
            $send .= " необходимо пройти регистрацию на сайте. Ниже, нажмите кнопку “Продолжить”";
            
            $host = "https://будь-здоров.рус/web";
            // $host = "http://localhost:8080";


            $recommender_id = null;

            if ($text_split[1] == "member") $action = "register";
            else if ($text_split[1] == "provider") $action = "register-provider";
            else {
                $split = explode("_", $text_split[1]);
                if ($split[0] == "member") {
                    $action = "register";
                    $recommender_id = $split[1];
                }
            }
            $url = "$host/profile/$action?tg=$chat_id";
            if ($recommender_id) {
                $url .= "&recommender_id=$recommender_id";
            }
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
        $send = "Вы зашли на тестовую страницу";
        
        $HideKeyboardMarkup = [ 'hide_keyboard' => true ];
        
        $bot->sendMessage($chat_id, $send, null, $HideKeyboardMarkup); 

        $send = "А зачем?";
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                // 'text' => "⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️\r\n⭐️Узнать в Яндексе⭐️\r\n⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️⭐️",
                'text' => "Узнать в Яндексе",
                'url' => "https://ya.ru"
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);


        return;
    }

    /***********************
    
           МОЯ ССЫЛКА

    ***********************/
    if ($text == "Моя ссылка" || $text == "/link")
    {
        $send = "Рекомендательская ссылка,  отправьте её для регистрации другу.";
        
        $bot->sendMessage($chat_id, $send);

        $send = "[https://t.me/bud_zdorov_rus_bot?start=member_$user->number](https://t.me/bud_zdorov_rus_bot?start=member_$user->number)";
        // $send = "[https://t.me/bud_zdorov_rus_bot?start=member_$user->id](https://t.me/bud_zdorov_rus_bot?start=member_$user->id)";
        // $send = "```https://t.me/bud_zdorov_rus_bot?start=member_$user->id```";
        
        $bot->sendMessage($chat_id, $send, "markdown");


        return;
    }

    /********************
    
            УСЛУГИ

    *********************/
    if ($text == "Услуги" || $text == "/service")
    {
        $send = "⭐️⭐️⭐️⭐️⭐️";
     
        $keyboard = [];
        
        if ($user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN || $chat_id == $admin || $chat_id == $master) 
        {
            array_push($keyboard, [ [ 'text' => 'Даты закупок' ] ]);
            array_push($keyboard, [ [ 'text' => 'Счета участников' ] ]);
        }
        else if ($user->role != User::ROLE_PROVIDER)
        {
            array_push($keyboard, [ [ 'text' => 'Закупки' ] ]);
        }
        
        array_push($keyboard, [ [ 'text' => 'Специалисты' ] ]);
        array_push($keyboard, [ [ 'text' => "⭐️Главное меню⭐️" ] ]);

        $ReplyKeyboardMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'selective' => true,
        ];

        $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);

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
                // [
                //     [ 'text' => 'Специалисты' ],
                // ],
                [
                    [ 'text' => 'Задать вопрос админу' ],
                ],
                [
                    [ 'text' => '⭐️Главное меню⭐️' ],
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
        // $KeyboardMarkup = [
        //     'keyboard' => [
        //         [
        //             [ 'text' => 'Задать вопрос админу' ],
        //         ],
        //     ],
        //     'resize_keyboard' => true
        // ];
        // $bot->sendMessage($chat_id, $send, null, $KeyboardMarkup);
        $keyboard = [];
        if ($user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN || $chat_id == $admin || $chat_id == $master) 
        {
            array_push($keyboard, [ [ 'text' => 'Даты закупок' ] ]);
        }
        else if ($user->role != User::ROLE_PROVIDER)
        {
            array_push($keyboard, [ [ 'text' => 'Закупки' ] ]);
        }
        $ReplyKeyboardMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'selective' => true,
        ];        
        $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);
        

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
                [
                    [ 'text' => '⭐️Главное меню⭐️' ],
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
        getBalance($bot, $chat_id);
        
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
        $send = "Существует три возможных варианта регистрации на сайте Будь-здоров.рус:

            1.    Упрощённая 
            2.    Полная
            3.    Регистрация поставщика

        Упрощённая регистрация позволяет Вам делать заказы из личного кабинета на сайте, но без предоставления скидок и накоплений.
        
        Что бы узнать какие возможности даёт “[Полная регистрация](https://будь-здоров.рус/web/category/454)” 👈 пройдите по ссылке.";
        
        $KeyboardMarkup = [
            'keyboard' => [
                [
                    [ 'text' => 'Упрощённая' ],
                    [ 'text' => 'Полная' ],
                ],
                [
                    [ 'text' => 'Регистрация поставщика товаров/услуг' ],
                ],
                [
                    [ 'text' => '⭐️Главное меню⭐️' ],
                ],
            ],
            'resize_keyboard' => true
        ];

        $bot->sendMessage($chat_id, $send, "markdown", $KeyboardMarkup);

        return;
    }

    
    /*****************************
    
        РЕГИСТРАЦИЯ ПОСТАВЩИКА

    ******************************/
    if ($text == "/regist_provider" || $text == "Регистрация поставщика товаров/услуг" || $text == "Регистрация поставщика")
    {
        if ($user) {
            $send = "Вы уже зарегестрированы.";
            $bot->sendMessage($chat_id, $send);    
            return;
        }

        $tg_com = TgCommunication::findOne(['chat_id' => $chat_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $chat_id;
        $tg_com->to_chat_id = $chat_id;
        $tg_com->from_whom = "registProviderFIO";
        $tg_com->save();

        $send = "В строке сообщение, укажите своё Ф.И.О.";
        $bot->sendMessage($chat_id, $send);

        return;
    }


    /******************************
    
        УПРОЩЁННАЯ регистрация

    *******************************/
    if ($text == "Упрощённая")
    {
        if ($user) {
            $send = "Вы уже зарегестрированы.";
            $bot->sendMessage($chat_id, $send);    
            return;
        }

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

    
    /*************************
    
        ПОЛНАЯ регистрация

    **************************/
    if ($text == "Полная")
    {
        if ($user) {
            $send = "Вы уже зарегестрированы.";
            $bot->sendMessage($chat_id, $send);    
            return;
        }
        
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
        
    
    /*******************
    
          НОВИЧКАМ

    *******************/
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
                    [ 'text' => '⭐️Главное меню⭐️' ]
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

    /***************************
    
            ПРИВЕТСТВИЕ 

    ****************************/
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

    /****************
    
          О НАС

    *****************/
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
    
    
    
    /******************************
    
        ЗАКУПКИ, управление ими

    *******************************/
    if ($text == "/purchase_date" || $text == "Даты закупок" || $text == "Закупки" || $text == "Показать все даты закупок" || $text == "Показать все категории закупок")
    {    
        $user = User::findOne(['tg_id' => $chat_id, 'disabled' => 0]);
        
        
        if ($user->role == User::ROLE_ADMIN || $user->role == User::ROLE_SUPERADMIN || $chat_id == $admin || $chat_id == $master) 
        {
            // для администраторов

            $providers = Provider::find()->where(['purchases_management' => 1])->all();

            $send = "Перечень поставщиков с ручным управлением закупками.";
                    
            $inline_keyboard = [];

            foreach ($providers as $provider) {
                array_push($inline_keyboard, [
                    [
                        'text' => $provider->name,
                        'callback_data' => 'providerpurchases_' . $provider->id
                    ]
                ]);
            }

            $InlineKeyboardMarkup = [
                'inline_keyboard' => $inline_keyboard
            ];  
            $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);
            
        }else if ($user->role != User::ROLE_PROVIDER)// || $chat_id == "351009636") 
        {            
            // для пайщиков

            $send = "⭐️⭐️⭐️⭐️⭐️";
            $keyboard = [];           

            $cart = CartTg::findOne(['tg_id' => $chat_id]);
            if ($cart) 
            {
                array_push($keyboard, [ [ 'text' => 'В корзине товар' ] ]);
            }

            array_push($keyboard, [ [ 'text' => 'Быстрый поиск товара' ] ]);
            
            $ReplyKeyboardMarkup = [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'selective' => true,
            ];
            $bot->sendMessage($chat_id, $send, null, $ReplyKeyboardMarkup);

            // getPurchasesOld($bot, $chat_id);
            
            // “Продукты” “Промтовары” “Здоровье”
            getMainPurchases($bot, $chat_id);
        
        } 
        
        return;
    }


    /***********************************
    
           ЗАКУПКИ по начатой дате 

    ************************************/
    if ($text == "/purchases_by_the_started_date" || $text == "Все закупки по начатой дате" || $text == "Показать закупки по начатой дате")
    {    
        continueSelection($bot, $chat_id, /*purchases_by_the_started_date=*/true);

        return;
    }


    /**********************
    
            КОРЗИНА 

    ***********************/
    if ($text == "/cart" || $text == "Корзина" || $text == "В корзине товар")
    {    
        getCart($bot, $chat_id);

        return;
    }

    
    /******************************
    
            СЧЕТА УЧАСТНИКОВ

    *******************************/
    if ($text == "Счета участников" || $text == "/accounts")
    {
        $tg_com = TgCommunication::findOne(['chat_id' => $chat_id]);
        if ( ! $tg_com ) {
            $tg_com = new TgCommunication();
        }
        $tg_com->chat_id = $chat_id;
        $tg_com->to_chat_id = $chat_id;
        $tg_com->from_whom = "accountsNumber";
        $tg_com->save();

        $send = "Внесите регистрационный номер участника в поле сообщения и отправьте его мне.";     
        $bot->sendMessage($chat_id, $send);

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
        
        $user = User::findOne(['tg_id' => $chat_id, 'disabled' => 0]);

        // редактирование цены товара и закупки
        if (strstr($tgCom->from_whom, '_', true) == 'editpriceproduct') 
        {
            $array = explode('_', $tgCom->from_whom);        
            $product_feature_id = $array[1];

            $price = $text;

            if ( ! is_numeric($price)) {
                $bot->sendMessage($chat_id, "Не верный формат числа");
                return;
            }

            $productFeature = ProductFeature::findOne($product_feature_id); 

            $product = Product::findOne($productFeature->product_id);
            $product_id = $product->id;
            $productPrice = ProductPrice::findOne(['product_feature_id' => $product_feature_id]);
            if ( ! $productPrice )
            {
                $productPrice = new ProductPrice();
                $productPrice->product_id = $product_id;
                // $productFeature = ProductFeature::findOne(['product_id' => $product_id]); 
                $productPrice->product_feature_id = $productFeature->id;
            }
            $productPrice->purchase_price = $price;
            $funds = Fund::find()->all();
            $percents = 0;
            foreach($funds as $fund) $percents = $percents + $fund->percent;
            $member_price = $price + ($price/100*$percents);
            $member_price = round($member_price, 2);
            $productPrice->member_price = $member_price;
            $price_all = $member_price + ($member_price/100*25);
            $price_all = round($price_all, 2);
            $productPrice->price = $price_all;
            if ( ! $productPrice->save() ) {
                $send = "Ошибка изменения цены " . $product->name;
                $bot->sendMessage($chat_id, $send);
            }else {
                $send = "Изменение цены на " . $product->name . ", произведено";
                $bot->sendMessage($chat_id, $send);

                $productFeatures = ProductFeature::find()->where(['product_id' => $product_id])->all(); 
                foreach($productFeatures as $productFeature) {
                    $purchaseProduct = PurchaseProduct::find()->where(['product_feature_id' => $productFeature->id])->andWhere(['status' => 'abortive'])->one();
                    if ($purchaseProduct)
                    {
                        $purchaseProduct->summ = $price;
                        $purchaseProduct->save();
                    }
                }
            }
            
            $tgCom->delete();
              
            $providerHasProduct = ProviderHasProduct::findOne(['product_id' => $product_id]);
            $provider_id = $providerHasProduct->provider_id;            
            $step = 1;

/*
            Эта часть из callbaqckQuery
            УПРАВЛЕНИЕ ЦЕНАМИ ЗАКУПОК
*/
            editPricePurchase($bot, $chat_id, $provider_id, $step);
            
            return;
        }
        

        // принятие новой даты заказа
        if (strstr($tgCom->from_whom, '_', true) == 'editstopdate') 
        {
            $array = explode('_', $tgCom->from_whom);        
            $provider_id = $array[1];

            $send = $text . "\r\nДата принята\r\n\r\nТеперь введите дату “Доставки” в формате: 15.11.2023";

            $date_timestamp = strtotime($text);
            if ( ! $date_timestamp ) {
                $bot->sendMessage($chat_id, "Не верный формат даты");            
                return;
            }
            
            $tgCom->from_whom = "editpurchasedate_" . $provider_id . "_" . $date_timestamp;
                
            $tgCom->save();
            $bot->sendMessage($chat_id, $send);
            
            return;
        }
        
        // редактирование закупки
        if (strstr($tgCom->from_whom, '_', true) == 'editpurchasedate') 
        {            
            $array = explode('_', $tgCom->from_whom);
            $provider_id = $array[1];            
            $stop_date = date('d.m.Y', $array[2]);
            $purchase_date = $text;
            
            if ( ! strtotime($purchase_date)) {
                $bot->sendMessage($chat_id, "Не верный формат даты");            
                return;
            }            
            
            if ( ! $provider_id) {
                $bot->sendMessage($chat_id, "Отсутсвуют данные: provider_id = null");            
                return;
            }      

            $provider = Provider::findOne($provider_id);
            $products = PurchaseProduct::find()->where(['provider_id' => $provider_id])->andWhere(['!=', 'status', 'held'])->all();
            
            foreach($products as $product) {
                $product->created_date = date('Y-m-d');
                $product->purchase_date = date('Y-m-d', strtotime($purchase_date));
                $product->stop_date = date('Y-m-d', strtotime($stop_date));
                $product->status = 'advance';
                $product->save();
            }

            $product = $products[0];

            // $feature_id = $product->product_feature_id;
            // $product_feature = ProductFeature::findOne($feature_id);
            // $real_product_id = $product_feature->product_id;
            // $real_product = Product::findOne($real_product_id);
            // $categoryHasProduct = CategoryHasProduct::findOne(['product_id' => $real_product_id]);
            // $category_id = $categoryHasProduct->category_id;
            // $category = Category::findOne($category_id);

            $send = date('d.m.Y') . "г., внесено изменение в график закупки товаров ";
            $send .= $provider->name . "\r\n";
            $send .= "Стоп заказ ".$stop_date."г. в 21 час.\r\n";
            $send .= "Доставка  ".$purchase_date."г."; 

            $InlineKeyboardMarkup = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Уведомить поставщика',
                            'callback_data' => 'notifyprovider_' . $provider_id
                        ],
                    ],
                    [
                        [
                            'text' => "Уведомить пайщиков",
                            'callback_data' => 'notifyShareholders_' . $provider_id
                        ],
                    ],
                    [
                        [
                            'text' => 'Изменить даты',
                            'callback_data' => 'editdatepurchase_' . $provider_id
                        ],
                    ],
                ]
            ];

            $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);
            $tgCom->delete();

            return;
        }

        
        // запрос количество товара, необходимого положить в корзину
        if (strstr($tgCom->from_whom, '_', true) == 'putInTheBasket') 
        {            
            $array = explode('_', $tgCom->from_whom);
            $product_feature_id = $array[1];
            $quantity = $text;
            if ( ! is_numeric($quantity) || $quantity < 1){
                $bot->sendMessage($chat_id, "Необходимо ввести положительное число!");            
                return;
            }            

            putInTheBasket($bot, $chat_id, $product_feature_id, $quantity);
            $tgCom->delete();

            return;
        }
        
        // запрос количество товара, необходимого изменить в корзине
        if (strstr($tgCom->from_whom, '_', true) == 'deleteOneProductPartly') 
        {            
            $array = explode('_', $tgCom->from_whom);
            $product_feature_id = $array[1];
            $quantity = $text;
            if ( ! is_numeric($quantity) || $quantity < 1){
                $bot->sendMessage($chat_id, "Необходимо ввести положительное число!");            
                return;
            }            

            deleteOneProductPartly($bot, $chat_id, $product_feature_id, $quantity);
            $tgCom->delete();

            return;
        }
        
        // запрос адреса для доставки товаров
        if (strstr($tgCom->from_whom, '_', true) == 'homeDelivery') 
        {            
            $array = explode('_', $tgCom->from_whom);
            $purchase_order_id = $array[1];
            $address = $text;

            homeDelivery($bot, $chat_id, $purchase_order_id, $address);
            $tgCom->delete();

            return;
        }
        
        // запрос ФИО поставщика
        if ($tgCom->from_whom == 'registProviderFIO') 
        {
            $tgCom->from_whom = "registProviderPhone_" . trim(preg_replace('/ /', "|", $text));
            $tgCom->save();

            $send = "Часть регистрации прошло успешно.\r\nУкажите для связи, свой номер телефона, в формате 8 963 555 3311 и отправьте сообщение.";
            $bot->sendMessage($chat_id, $send);

            return;
        }

        // запрос телефона поставщика
        if (strstr($tgCom->from_whom, '_', true) == 'registProviderPhone') 
        {            
            $array = explode('_', $tgCom->from_whom);
            $fio = $array[1];
            $phone = preg_replace('/ /', "", $text);
            
            $send = "Перейдя к дальнейшей регистрации, расскажите коротко о своей услуге или предлагаемом товаре, а также укажите пароль и повторите его.";
            
            $InlineKeyboardMarkup = [
                'inline_keyboard' => [[[
                    'text' => 'Перейти к дальнейшей регистрации',
                    'url' => "https://Будь-здоров.рус/web/profile/register-small?role=provider&fio=".$fio."&phone=".$phone."&tg=".$chat_id
                ]]]
            ];
            $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);
            
            $tgCom->delete();

            return;
        }

        // запрос регистрационного номера участника
        if ($tgCom->from_whom == 'accountsNumber') 
        {            
            $number = preg_replace('/ /', "", $text);

            if ( ! is_numeric($number) ) {
                $bot->sendMessage($chat_id, "Необходимо ввести положительное число!");            
                return;
            }
            
            getBalanceByNumber($bot, $chat_id, $number);

            $user = User::findOne(['number' => $number, 'disabled' => 0]);

            $send = "Укажите в строке сообщени, сумму зачисления или списания для $user->firstname $user->patronymic Рег.№ $number";            
            $bot->sendMessage($chat_id, $send);
            
            $tgCom->from_whom = "editDeposit_$number";
            $tgCom->save();

            return;
        }
        
        // изменение расчётного счёта контрагента
        if (strstr($tgCom->from_whom, '_', true) == 'editDeposit') 
        {                    
            $array = explode('_', $tgCom->from_whom);
            $number = $array[1];

            $summa = preg_replace('/ /', "", $text);

            if ( ! is_numeric($summa) ) {
                $bot->sendMessage($chat_id, "Необходимо ввести число!");            
                return;
            }

            $user = User::findOne(['number' => $number, 'disabled' => 0]);
            // $deposit = $user->getAccount(Account::TYPE_DEPOSIT);

            $message = "";
            if ($summa > 0) {
                // $message = "Зачисление средств админом через телеграм";
                $message = "Зачисление средств";
            }else {
                // $message = "Списание средств админом через телеграм";
                $message = "Списание средств";
            }
            if (!Account::transfer($user->deposit, null, $user, $summa, $message, true)) {                    
                $bot->sendMessage($chat_id, "Ошибка сохранения счета!");            
                return;
            }

            Email::tg_send('new-account-log', $chat_id, [
                'role' => getRole($user),
                'number' => $number,
                'message' => $message,
                'amount' => $summa,
                'total' => $user->deposit->total,
                'invest' => $user->bonus->total,
                'pay' => getPay($user),
            ]);
            
            $tgCom->delete();

            return;
        }
        

        if ( ! $tgCom->from_whom || $tgCom->from_whom == "client") {
            if ( ! $user || $user->lastname == "lastname") {
                $send = "Не зарегистрированный пользователь". "\r\n\r\n" . $text;
            }else {
                // $send = "Сообщение от пользователя №" . $chat_id . "\r\n\r\n" . $text;
                $send = "Сообщение от клиента" . "\r\n\r\n" . $text;                  
            }     
        }else {
            $send = "Сообщение от специалиста" . "\r\n\r\n" . $text;
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

        if ($tgCom->from_whom && $tgCom->from_whom == "specialist") {
            $send = "Ваше сообщение отправлено";
        }else {
            $send = "Ваше сообщение отправлено, при наличии времени специалист с вами сразу свяжется";
        }

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


