<?php 

namespace app\modules\bots\api;

class Telegraph {

    // $access_token - созданный токен для Telegraph
    private $access_token = null;
    // адрес для запросов к API Telegraph
    public $apiUrl = "https://api.telegra.ph/";
	
	public $data = null;
    
	/*
	** @param str $access_token
	*/
    public function __construct($access_token)
    {
        $this->access_token = $access_token;
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
	** Отправляем запрос в Телеграф
	**
    ** @param str $method
    ** @param array $data    
	**
    ** @return mixed
    */
    public function call($method, $data = null)
    {        
        $result = null;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $method);
        if (is_array($data)) {
            if ($method != "createAccount") $data['access_token'] = $this->access_token;
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
	**  функция создания аккаунта
	**
	**  @param str $short_name
 	**  @param str $author_name
	**  @param str $author_url
	**  
	**  @return object
	*/
    public function createAccount(
        $short_name,  
        $author_name,  
        $author_url = null        
    ) {
        $response = $this->call("createAccount",[
            'short_name' => $short_name,
            'author_name' => $author_name,
            'author_url' => $author_url,
        ]);        
        $response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
    }

    
    
    /*
	**  функция создания страницы
	**
 	**  @param str $title
	**  @param str $author_name
	**  @param str $author_url
	**  @param array $content	// '[{"tag":"p","children":["Hello,+world!"]}]'
	**  @param bool $return_content
	**  
	**  @return object
	*/
    public function createPage(
		$title, // max length 256
		$content, // max 64Kb
		$author_name = null, // max length 128
		$author_url = null, // max length 512
		$return_content = false
	) {
		$response = $this->call("createPage", [
			'title' => $title,
			'content' => $content,
			'author_name' => $author_name,			
			'author_url' => $author_url,
			'return_content' => $return_content
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}
    
    
    /*
	**  функция редактирования страницы
	**
 	**  @param str $path
 	**  @param str $title
	**  @param str $author_name
	**  @param str $author_url
	**  @param array $content	// '[{"tag":"p","children":["Hello,+world!"]}]'
	**  @param bool $return_content
	**  
	**  @return object
	*/
    public function editPage(
		$path,
		$title, // max length 256
		$content, // max 64Kb
		$author_name = null, // max length 128
		$author_url = null, // max length 512
		$return_content = false
	) {
		$response = $this->call("editPage", [
			'path' => $path,
			'title' => $title,
			'content' => $content,
			'author_name' => $author_name,			
			'author_url' => $author_url,
			'return_content' => $return_content
		]);	
				
		$response = json_decode($response);
		
		if ($response && $response->ok) {
			$response = $response->result;
		}else $response = false;
		
		return $response;
	}


}

?>