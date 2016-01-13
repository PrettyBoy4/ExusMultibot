<?php
class advertisement extends start
{
  private $multibotCore;
  private $tsAdmin;
  protected $start_timer = 0;
  protected $function_name = "advertisement";

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
  	$config = $this->multibotCore->getConfig('multibot');	
  	
  	$aml = $tsAdmin->whoAmI	();
  	
  $server_id = $aml['data']['virtualserver_id'];
  	
  	$lol = $tsAdmin->sendMessage(3, $server_id, $config['advertisement']['general_config']['message']);

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
    $this->tsAdmin = $multibotCore->getTsAdmin();
    $this->multibotCore = $multibotCore;
  }
  
}
?>