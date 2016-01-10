<?php
/** adminStatus($multibotCore)
  *
  * Wersja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Wypisuje status administracji do opsiu kanału
  *
  * Wymagane zmienne multibotCore:
  * - serverGroupNames (Zalecany czas odświeżania: Dość długi ponieważ nie ma potrzeby częstego odświeżania nazw grup serwera)
  *
  */
class adminStatus extends start
{
  private $multibotCore;
  protected $start_timer = 0;
  protected $function_name = "Admin Status";
/** start_function()
  *
  * Typ: Chroniona
  *
  * Główny moduł funkcji adminStatus
  *
  */
  protected function start_function()
  {
  	$config = $this->multibotCore->getConfig("multibot");
    $tsAdmin = $this->multibotCore->getTsAdmin();
    $messageFinish = '';
    $groupList = explode(",", $config['adminstatus']['adminstatus']['groups']);
    foreach($groupList as $groupListTemp)
    {
      $clientList = $tsAdmin->serverGroupClientList($groupListTemp, true);
      $serverGroupNames = $this->multibotCore->getServerGroupNames();
      if(isset($serverGroupNames['regular'][$groupListTemp])) {
        $groupName = $serverGroupNames['regular'][$groupListTemp]['name'];
        $lista_administratorow = $config['adminstatus']['adminstatus']['administrator'];
        $groupName = str_replace('{group}', $groupName, $config['adminstatus']['adminstatus']['group_name']);
        $messageFinish = $messageFinish.$groupName."\n";
        if($clientList['success'] && (!empty($clientList['data'])))
        foreach($clientList['data'] as $clientListTemp)
        {
          $message = str_replace('{admin}', $config['adminstatus']['adminstatus']['admin'],$lista_administratorow);
          $status = $tsAdmin->clientGetIds($clientListTemp['client_unique_identifier']);
          if($status['success'])
          {
            $message = str_replace('{status}', $config['adminstatus']['adminstatus']['online'],$message);
            $message = str_replace('{client_nickname}', $clientListTemp['client_nickname'],$message);
            $messageFinish = $messageFinish.$message;
          }
          else
          {
            $message = str_replace('{status}', $config['adminstatus']['adminstatus']['offline'],$message);
            $message = str_replace('{client_nickname}',$clientListTemp['client_nickname'],$message);
            $messageFinish = $messageFinish.$message;
          }
        }
      }
    }
    $channel['channel_description'] = $config['adminstatus']['adminstatus']['header'].$messageFinish;
    $lol = $tsAdmin->channelEdit($config['adminstatus']['adminstatus']['channel'], $channel);
    return 'AdminStatus';
  }
  /** __construct($multibotCore)
    *
    * Typ: Konstruktor
    *
    */
    function __construct(multibotCore $multibotCore)
    {
      $this->multibotCore = $multibotCore;
    }
}
?>