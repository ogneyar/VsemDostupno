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

    /*
	**  функция пересылки сообщения 
	**
	**  @param int $chat_id
	**  @param int $message_thread_id
	**  @param int $from_chat_id
	**  @param bool $disable_notification
	**  @param bool $protect_content
	**  @param int $message_id
	**  
	**  @return array
	*/
    public function forwardMessage(
		$chat_id, 
		$from_chat_id,
		$message_id,
		$message_thread_id = null,
		$disable_notification = false,
		$protect_content = false
	) {
				
		$response = $this->call("forwardMessage", [
			'chat_id' => $chat_id,
			'from_chat_id' => $from_chat_id,
			'message_id' => $message_id,			
			'message_thread_id' => $message_thread_id,
			'disable_notification' => $disable_notification,
			'protect_content' => $protect_content
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}

    /*
	**  функция копирования сообщения 
	**
	**  @param int $chat_id
	**  @param int $message_thread_id
	**  @param int $from_chat_id
	**  @param int $message_id
	**  @param str $caption
	**  @param str $parse_mode
	**  @param array $caption_entities
	**  @param bool $disable_notification
	**  @param bool $protect_content
	**  @param int $reply_to_message_id
	**  @param bool $allow_sending_without_reply
	**  @param array $reply_markup
	**  
	**  @return array
	*/
    public function copyMessage(
		$chat_id, 
		$from_chat_id,
		$message_id,
		$caption = null,
		$parse_mode = null,
		$message_thread_id = null,
		$caption_entities = null,
		$disable_notification = false,
		$protect_content = false,
		$reply_to_message_id = null,
		$allow_sending_without_reply = false,
		$reply_markup = null
		) {

		if ($reply_markup) $reply_markup = json_encode($reply_markup);
				
		$response = $this->call("copyMessage", [
			'chat_id' => $chat_id,
			'message_thread_id' => $message_thread_id,
			'from_chat_id' => $from_chat_id,
			'message_id' => $message_id,			
			'caption' => $caption,
			'parse_mode' => $parse_mode,
			'caption_entities' => $caption_entities,
			'disable_notification' => $disable_notification,
			'protect_content' => $protect_content,
			'reply_to_message_id' => $reply_to_message_id,
			'allow_sending_without_reply' => $allow_sending_without_reply,
			'reply_markup' => $reply_markup
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}

    /*
	**  функция удаления сообщения 
	**
	**  @param int $chat_id
	**  @param int $message_id
	**  
	**  @return array
	*/
    public function deleteMessage(
		$chat_id, 
		$message_id
		) {

		$response = $this->call("deleteMessage", [
			'chat_id' => $chat_id,
			'message_id' => $message_id
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}

    /*
	**  функция отправки голосового сообщения 
	**
	**  @param int $chat_id
	**  @param int $message_thread_id
	**  @param str $voice
	**  @param str $caption
	**  @param str $parse_mode
	**  @param array $caption_entities
	**  @param int $duration
	**  @param bool $disable_notification
	**  @param bool $protect_content
	**  @param int $reply_to_message_id
	**  @param bool $allow_sending_without_reply
	**  @param array $reply_markup
	**  
	**  @return array
	*/
    public function sendVoice(
		$chat_id, 
		$voice,
		$caption = null,
		$parse_mode = null,
		$duration = null,
		$message_thread_id = null,
		$caption_entities = null,
		$disable_notification = false,
		$protect_content = false,
		$reply_to_message_id = null,
		$allow_sending_without_reply = false,
		$reply_markup = null
		) {

		if ($reply_markup) $reply_markup = json_encode($reply_markup);
				
		$response = $this->call("sendVoice", [
			'chat_id' => $chat_id,
			'message_thread_id' => $message_thread_id,
			'voice' => $voice,
			'caption' => $caption,
			'parse_mode' => $parse_mode,
			'caption_entities' => $caption_entities,
			'duration' => $duration,			
			'disable_notification' => $disable_notification,
			'protect_content' => $protect_content,
			'reply_to_message_id' => $reply_to_message_id,
			'allow_sending_without_reply' => $allow_sending_without_reply,
			'reply_markup' => $reply_markup
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}

	
	/*
	**  функция отправки видео
	**
	**  @param int $chat_id
 	**  @param str $video
	**  @param str $caption
	**  @param str $parse_mode
	**  @param array $reply_markup
	**  @param int $reply_to_message_id	
	**  @param int $duration
	**  @param int $width
	**  @param int $height
	**  @param str $thumb
	**  @param bool $disable_notification
	**  @param bool $supports_streaming
	**  
	**  @return array
	*/
    public function sendVideo(
		$chat_id, 
		$video,		
		$caption = null,
		$parse_mode = null,
		$reply_markup = null,
		$reply_to_message_id = null,		
		$duration = null,
		$width = null,
		$height = null,
		$thumb = null,
		$disable_notification = false,
		$supports_streaming = false
	) {
		
		if ($reply_markup) $reply_markup = json_encode($reply_markup);
		
		$response = $this->call("sendVideo", [
			'chat_id' => $chat_id,
			'video' => $video,
			'duration' => $duration,
			'width' => $width,
			'height' => $height,
			'thumb' => $thumb,
			'caption' => $caption,
			'parse_mode' => $parse_mode,		
			'supports_streaming' => $supports_streaming,
			'disable_notification' => $disable_notification,
			'reply_to_message_id' => $reply_to_message_id,
			'reply_markup' => $reply_markup
		]);	
				
		$response = json_decode($response, true);
		
		if ($response['ok']) {
			$response = $response['result'];
		}else $response = false;
		
		return $response;
	}
	
	
	
}