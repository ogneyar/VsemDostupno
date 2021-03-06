<?php

namespace app\modules\bots\controllers;

use Yii;
use yii\web\Controller;
use app\modules\bots\api\Bot;
use app\models\User;
use app\models\Forgot;
use app\models\Email;



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

    if ($text == "/start") {
        $send = "Здравствуй " . $first_name . "!\r\n\r\n";
        $send .= "Чтобы узнать свой chat_id, нажми кнопку ниже.";
        $InlineKeyboardMarkup = [
            'inline_keyboard' => [[[
                'text' => 'Информация',
                'callback_data' => 'information'
            ]]]
        ];
        $bot->sendMessage($chat_id, $send, null, $InlineKeyboardMarkup);
        return;
    }

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
                
        }
    }

}

function requestCallbackQuery($bot, $callback_query) {
    $from = $callback_query['from'];
        $from_id = $from['id'];
    $message = $callback_query['message'];
        $message_from = $message['from'];
            $message_from_first_name = $message_from['first_name'];
        $chat = $message['chat'];
            $chat_id = $chat['id'];
        $text = $message['text'];
    
    $send = "Твой chat_id: \r\n\r\n" . $from_id;
    $bot->sendMessage($from_id, $send);
}
