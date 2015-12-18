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
  protected $timer = 0;
/** start_function()
  *
  * Typ: Chroniona
  *
  * Główny moduł funkcji adminStatus
  *
  */
  protected function start_function()
  {
    $tsAdmin = $this->multibotCore->tsAdmin;
    $messageFinish = '';
    $groupList = explode(",", $this->multibotCore->config['multibotConfig']['adminStatus']['groups']);
    foreach($groupList as $groupListTemp)
    {
      $clientList = $tsAdmin->serverGroupClientList($groupListTemp, true);
      if(isset($this->multibotCore->serverGroupNames['regular'][$groupListTemp])) {
        $groupName = $this->multibotCore->serverGroupNames['regular'][$groupListTemp]['name'];
        $lista_administratorow = $this->multibotCore->config['multibotConfig']['adminStatus']['administrator'];
        $groupName = str_replace('{group}', $groupName, $this->multibotCore->config['multibotConfig']['adminStatus']['group_name']);
        $messageFinish = $messageFinish.$groupName."\n";
        if($clientList['success'] && (!empty($clientList['data'])))
        foreach($clientList['data'] as $clientListTemp)
        {
          $message = str_replace('{admin}',$this->multibotCore->config['multibotConfig']['adminStatus']['admin'],$lista_administratorow);
          $status = $tsAdmin->clientGetIds($clientListTemp['client_unique_identifier']);
          if($status['success'])
          {
            $message = str_replace('{status}',$this->multibotCore->config['multibotConfig']['adminStatus']['online'],$message);
            $message = str_replace('{client_nickname}',$clientListTemp['client_nickname'],$message);
            $messageFinish = $messageFinish.$message;
          }
          else
          {
            $message = str_replace('{status}',$this->multibotCore->config['multibotConfig']['adminStatus']['offline'],$message);
            $message = str_replace('{client_nickname}',$clientListTemp['client_nickname'],$message);
            $messageFinish = $messageFinish.$message;
          }
        }
      }
    }
    $channel['channel_description'] = $this->multibotCore->config['multibotConfig']['adminStatus']['header'].$messageFinish;
    $tsAdmin->channelEdit($this->multibotCore->config['multibotConfig']['adminStatus']['channel'], $channel);
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
