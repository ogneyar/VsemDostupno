<?php

namespace app\modules\bots\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\bots\api\Bot;

require __DIR__ . '/../requests/processing.php';


class DefaultController extends Controller
{
    // можно так отключить проверку csrf-токена
    public $enableCsrfValidation = false;
    
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

