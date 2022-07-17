<?php 

namespace app\modules\bots\api;

class Bot {

    // $token - созданный токен для нашего бота от @BotFather
    private $token = null;
    // адрес для запросов к API Telegram
    public $apiUrl = "https://api.telegram.org/bot";
	
	public $fileUrl = "https://api.telegram.org/file/bot";

	public $data = null;
    
	/*
	** @param str $token
	*/
    public function __construct($token)
    {
        $this->token = $token;
    }    
    
	/*
	** @param JSON $data_php
	** @return array
	*/
    public function init($data)
    {
        // создаем массив из пришедших данных от API Telegram
        $this->data = json_decode(file_get_contents($data), true);    
		return $this->data;
    }
	    
    
    /* 
	** Отправляем запрос в Телеграмм
	**
    ** @param str $method
    ** @param array $data    
	**
    ** @return mixed
    */
    public function call($method, $data = null)
    {
        // return file_get_contents($this->apiUrl . $this->token . '/' . $method);
        
        $result = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $this->token . '/' . $method);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }

    /*
	**  функция вывода на печать массива
	**
	**  @param array $mass
	**  @param int $i
	**  @param str $flag
	**
	**  @return string
	*/
	public function PrintArray($mass, $i = 0) {		
        $response = "";
		global $flag;			
		$flag .= "\t\t\t\t";						
		foreach($mass as $key[$i] => $value[$i]) {				
			if (is_array($value[$i])) {			
					$response .= $flag . $key[$i] . " : \n";					
					$response .= $this->PrintArray($value[$i], ++$i);					
			}else $response .= $flag . $key[$i] . " : " . $value[$i] . "\n";			
		}		
		$str = $flag;		
		$flag = substr($str, 0, -4);		
		return $response;		
	}


    /*
    ** @return object
    */
    public function setWebhook($url) {
        $response = $this->call("setWebhook",[
            'url' => $url
        ]);        
        $response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
    }
    
    /*
    ** @return object
    */
    public function getMe()
    {
        $response = $this->call("getMe");
        $response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
    }

    /*
	**  функция отправки сообщения 
	**
	**  @param int $chat_id
 	**  @param str $text
	**  @param str $parse_mode
	**  @param array $reply_markup
	**  @param int $reply_to_message_id	
	**  @param bool $disable_web_page_preview
	**  @param bool $disable_notification
	**  
	**  @return array
	*/
    public function sendMessage(
		$chat_id, 
		$text,
		$parse_mode = null,
		$reply_markup = null,
		$reply_to_message_id = null,
		$disable_web_page_preview = false,
		$disable_notification = false
	) {
		
		if ($reply_markup) $reply_markup = json_encode($reply_markup);
		
		$response = $this->call("sendMessage", [
			'chat_id' => $chat_id,
			'text' => $text,
			'parse_mode' => $parse_mode,			
			'disable_web_page_preview' => $disable_web_page_preview,
			'disable_notification' => $disable_notification,
			'reply_to_message_id' => $reply_to_message_id,
			'reply_markup' => $reply_markup
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}
	
}