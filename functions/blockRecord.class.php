<?php
/** welcomeMessage($multibotCore)
  *
  * Wrsja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Wysyła wiadomość powitalną do każdego nowego użytkownika
  *
  * Wymagane zmienne (multibotCore):
  * - clientList
  *
  */
class blockRecord extends start
{
  private $multibotCore;
  private $tsAdmin;
  protected $start_timer = 0;
  protected $function_name = "Block Record";

/** start_function()
  *
  * Typ: Publiczna
  *
  * Główna funkcja welcomeMessage
  *
  * Wymagane zmienne
  * -serverInfo
  *
  */
  public function start_function()
  {
  	$tsAdmin = $this->tsAdmin;
  	$client_list = $this->multibotCore->getClientList();
  	$config = $this->multibotCore->getConfig('multibot');	
  	
  	foreach($client_list as $clid => $client_info) {
  		
  		if($client_info['client_is_recording']) {
			$lol = $tsAdmin->clientKick($clid, $config['blockrecord']['action']['kick_from'], $config['blockrecord']['action']['message']);
			print_r($lol);
  		}
  	}
  }
  
/** __construct($multibotCore)
  *
  * Typ: Konstruktor
  *
  * Przypisuje referencje
  *
  */
  function __construct(multibotCore $multibotCore)
  { // Konstruktor jako argument przyjmuje obiekt multibotCore
    $multibotCore->refresh('clientList'); // Wymuszenie załadowania licty użytkowników w celu przypisania ich do listy wysłanych po to aby aktualnie obecni użytkownicy przebywający serwerze w czasie uruchamiania bota nie dostali wiadokości powitalnej
    $this->tsAdmin = $multibotCore->getTsAdmin();
    $this->multibotCore = $multibotCore;
  }
  
}
?>