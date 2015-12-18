<?php
/**
 *                         multibot-coreclass.php
 *                         ------------------
 *   Początek projektu      : 10. Listopad 2015
 *   Prawa Autorskie        : Karol Krupa (Exus)
 *   E-mail                 : karo2krupa@gmail.com
 *   Wersja                 : 1.0.0 Alpha
 *   Ostatnia modyfikacja   : 18. Grudzień 2015
 *
 *
 *  Plik ten zawiera cały kod klasy multibota niezbędnej każdemu
 *  procesowi multibota. Klasa ta jest głownym "silnikiem" wszystkich funkcji
 *  przechowuje, modyfikuje i odświrza informacje o serwerze, użytkownikach, kanałąch itp.
 *  i udostępnie je każdej fukncji która tych danych potrzebuje.
 *  Dzięki zastosowaniu takiego rozwiozania znacznie zmniejszamy ilość połączeń oraz
 *  obciążenia serwera ts3 dzięki zapobieganiu pobieraniu wielu razy tych samych
 *  informacji przez każdą funkcje.
 *
 */


/** Publiczne zmienne:
  *
  * Zmienne ładowane są dynamicznie w zależności od dołączonych i uruchomionych funkji.
  * Domyślnie wszystkie zmienne są pustą tablicą i aby z nich kożystać trzeba poprosić
  * kontroler o utworzenie oraz ustalić czas odświeżania.
  *
  * Oficjalne funkcje uruchamiana przez kontroler mają przypisane wyamagane zmienne
  * w razie chęci stworzenie nowej funkcji korzystającej z multibotCore należy
  * stworzyć odpowiedni wpis w kontrolerze lub ręcznie wymusić utworzenie odpowiednich
  * zmiennych funkcją refresh(). Zaleca się kożystanie ze zmiennych udostępnianych w
  * multibotCore ponieważ przyspiesza to działanie całego multibota dzięki czasowemu
  * ładowaniu zmiennych. Należy pamiętać że ładowanie niektórych informacji z ts3admin
  * trwa dłuższy czas. Informacjami takimi w momencie pisania tego opisu była funkcja
  * "channelInfo()" która wykonywała się znacznie dłużej od innych co skutkowało
  * widocznym spowolnieniem całego multibota.
  *
  * $tsAdmin - Przechowuje referencje do obiektu ts3admin tworzonego wewnątrz
  * obiektu multibotCore.
  *
  * $channelList - Przechowuje listę kanałów serwerze.
  *
  * $clientList - Przechowuje tablicę zawierającą listę wszystkich użytkowników
  * obecnych na serwerze.
  *
  * $serverInfo - Przechowuje tablice z informacjami o serwerze.
  *
  * $config - Przechowuje tablice z konfiguracją.
  *
  * $serverGroupNames - Przechowuje tablice z nazwami grup serwerowych.
  *
  * Wszystkie zmienne można odświeżyć za pomocą funkcji refresh('nazwa_zmiennej')
  * np. refresh('config').
  *
  */


/** start
  *
  * Klasa uruchamiająca funkcje bota w odpowiednich przedziałąch czasowych.
  * Każda funkcja ją dziedziczy dzięki czemu w każdej można użyć tej funkcji.
  *
  */
class start
{
/** start($time)
  *
  * Typ: Publiczna
  *
  * Uruchamia timer który uruchamia funkcję w odpowiednim przedziale czasowym
  *
  */
  public function start($time = 0)
  {
    //if(empty($this->timer))
    //{
      //$this->timer = date("r");
    //}

    if($this->timer <= date("r"))
    {
      $start = $this->start_function();

      if(empty($this->timer)) {
        if(empty($start) && empty($this->functionName)) {
          $start = 'UNKNOWN FUNCTION';
        }elseif(!empty($this->functionName)) {
          $start = $this->functionName;
        }
        print 'START FUNCTION: '.$start."\n";
      }

      $this->timer = date("r", time() + $time);
    }
  }
}

class multibotCore {


  //*******************************************************************************************
  //****************************************** Vars *******************************************
  //*******************************************************************************************




  public $files = Array('ts3admin' => 'Core/ts3admin.class.php', 'general-config' => 'Configs/general-config.ini', 'permissions' => 'Configs/permissions.ini', 'function-list' => 'Configs/function-list.ini', 'commands-dir' => 'Commands/', 'function-dir' => 'Functions/', 'socketFile' => 'Core/socketInternal.sock');

  private $langFiles = Array('pl' => 'Locales/pl.ini');
  private $lang;

  private $refresh = Array();

  public $tsAdmin;
  public $channelList = Array();
  public $clientList = Array();
  public $serverInfo = Array();
  public $config = Array();
  public $serverGroupNames = Array();





//*******************************************************************************************
//************************************ Public Functions *************************************
//******************************************************************************************


function getClientList($query = false) {
  if($query)  {
    return $this->clientList;
  }else {
    foreach($this->clientList as $clients)  {
      if($clients['client_type'] == 0)  {
        $return[] = $clients;
      }
    }
    return $return;
  }
}









/** getLang();
  *
  * Zwraca używany język
  */
function getLang()  {
  return $this->lang;
}










/** setName()
  *
  * Ustawia nazwę połączonej instancji
  */
function setName($name) {
  if(!$this->tsAdmin->getElement('success', $this->tsAdmin->setName($name)))  {
    $this->addError($this->lnag['set_bot_name_error'], ture, false);
    return false;
  }else {
    $this->addInfo($this->lang['set_bot_name_success']);
    return true;
  }
}










/** refreshConfig()
  *
  * Odświerza konfiguracje
  */
function refreshConfig()  {
  $config = parse_ini_file($this->files['general-config'], true);

  if(empty($config))  {
    $this->addError($this->lang['config_file_loading_error'], false, true);
  }else {
    $this->addInfo($this->lang['config_file_loading_success']);
  }


  $permissionsLoad = parse_ini_file($this->files['permissions']);

  if(empty($permissionsLoad)) {
    $this->addError($this->lang['permissions_file_loading_error'], false, true);
  }else {
    $this->addInfo($this->lang['permissions_file_loading_success']);
  }

  $permissions = Array();

  foreach($permissionsLoad as $dbid => $perms) {
    $perms = preg_replace('/\s+/', '', $perms);
    $perm = explode(",", $perms);
    foreach($perm as $permTemp) {
      if(isset($permissions[$permTemp]) && !empty($permissions[$permTemp])) {
        $permissions[$permTemp] .= ",".$dbid;
      }else {
        $permissions[$permTemp] = $dbid;
      }
    }
  }

  $this->config['permissions'] = $permissions;


  //multibotConfig

  $functionList = parse_ini_file($this->files['function-list'], true);

  if(empty($functionList))  {
    $this->addError($this->lang['function-list_file_lading_error'], false, true);
  }else {
    $this->addInfo($this->lang['function-list_file_loading_success']);
  }

  foreach($functionList as $functionName => $functionFiles) {
    if(!isset($multibotConfig[$functionName]))  {
      $multibotConfig[$functionName] = parse_ini_file("Configs/Functions/". $functionFiles['config']);

      if(empty($multibotConfig[$functionName]) || !isset($multibotConfig[$functionName]))  { $this->addError($this->lang['file_loading_error']. $functionFiles['config']); }
    }else {
      $this->addError($this->lang['multibot_function_config_exist']. $functionName);
    }
  }

  foreach($this->config['multibotConfig'] as $name => $value) {
    if(isset($multibotConfig[$name]['enable']) && isset($this->config['multibotConfig'][$name]['enable']))  {
      $multibotConfig[$name]['enable'] = $this->config['multibotConfig'][$name]['enable'];
    }
  }

  $this->config['multibotConfig'] = $multibotConfig;

  return true;
}
/** refres($var, $time)
  *
  * $var - Nazwa zmiennej do odświeżenia lub utworzenia
  * $time - Odstęp czasu między kolejnymi odświeżeniami
  *
  * Typ: Publiczna
  *
  * odświeża lub przypisuje wartość do zmiennej w multibotCore
  *
  */
function refresh($var, $time = 0)
{
  if(empty($this->refresh[$var]))
  {
    $this->refresh[$var] = date("r");
    //$function = "refresh".$var;
  }

  if($this->refresh[$var] <= date("r"))
  {
    $function = "refresh".$var;
    $this->$function();

    $this->refresh[$var] = date("r", time() + $time);
    return true;
  }
}










/** prepareMessage($file, $clid = null)
  *
  * $file - Nazwa pliku do wczyatania (Ustalona w konfiguracji).
  * $clid - id użytkownika którego dane mają być wczytane.
  *
  * Typ: Publiczna
  *
  * Zamienia tagi {tag} na odpowiednie ciagi.
  *
  * Wymagane zmienne: $serverInfo.
  */
function prepareMessage($file, $clid = null)
{

  if($clid != null)
  {

    $clientInfoTemp = $this->tsAdmin->clientInfo($clid);
    $clientInfo = $clientInfoTemp['data'];
    unset($clientInfoTemp);

    $clientInfo['client_created'] = date('d-m-Y H.i.s', $clientInfo["client_created"]);

    $clientInfo['client_lastconnected'] = date('d-m-Y H.i.s', $clientInfo["client_lastconnected"]);

    $this->serverInfo['virtualserver_uptime'] = $this->tsAdmin->convertSecondsToStrTime($this->serverInfo['virtualserver_uptime']);

    $varsMessage = Array(
    "{cid}",
    "{client_unique_identifier}",
    "{client_nickname}",
    "{client_version}",
    "{client_platform}",
    "{client_database_id}",
    "{client_created}",
    "{client_lastconnected}",
    "{client_totalconnections}",
    "{client_description}" ,
    "{client_month_bytes_uploaded}",
    "{client_month_bytes_downloaded}",
    "{client_total_bytes_uploaded}",
    "{client_total_bytes_downloaded}",
    "{client_nickname_phonetic}",
    "{client_country}",
    "{client_base64HashClientUID}",
    "{connection_filetransfer_bandwidth_sent}",
    "{connection_filetransfer_bandwidth_received}",
    "{connection_packets_sent_total}",
    "{connection_bytes_sent_total}",
    "{connection_packets_received_total}",
    "{connection_bytes_received_total}",
    "{connection_bandwidth_sent_last_second_total}",
    "{connection_bandwidth_sent_last_minute_total}",
    "{connection_bandwidth_received_last_second_total}",
    "{connection_bandwidth_received_last_minute_total}",
    "{connection_connected_time}",
    "{connection_client_ip}",
    "{virtualserver_uptime}",
    "{virtualserver_maxclients}",
    "{virtualserver_reserved_slots}",
    "{virtualserver_clientsonline}",
    "{virtualserver_channelsonline}",
    "{virtualserver_welcomemessage}",
    "{virtualserver_name}",
    "{virtualserver_client_connections}",
    "{virtualserver_min_client_version}",
    "{date}"
    );

    $valuesMessage = Array(
    $clientInfo['cid'],
    $clientInfo['client_unique_identifier'],
    $clientInfo['client_nickname'],
    $clientInfo['client_version'],
    $clientInfo['client_platform'],
    $clientInfo['client_database_id'],
    $clientInfo['client_created'],
    $clientInfo['client_lastconnected'],
    $clientInfo['client_totalconnections'],
    $clientInfo['client_description'],
    $clientInfo['client_month_bytes_uploaded'],
    $clientInfo['client_month_bytes_downloaded'],
    $clientInfo['client_total_bytes_uploaded'],
    $clientInfo['client_total_bytes_downloaded'],
    $clientInfo['client_nickname_phonetic'],
    $clientInfo['client_country'],
    $clientInfo['client_base64HashClientUID'],
    $clientInfo['connection_filetransfer_bandwidth_sent'],
    $clientInfo['connection_filetransfer_bandwidth_received'],
    $clientInfo['connection_packets_sent_total'],
    $clientInfo['connection_bytes_sent_total'],
    $clientInfo['connection_packets_received_total'],
    $clientInfo['connection_bytes_received_total'],
    $clientInfo['connection_bandwidth_sent_last_second_total'],
    $clientInfo['connection_bandwidth_sent_last_minute_total'],
    $clientInfo['connection_bandwidth_received_last_second_total'],
    $clientInfo['connection_bandwidth_received_last_minute_total'],
    $clientInfo['connection_connected_time'],
    $clientInfo['connection_client_ip'],
    $this->serverInfo['virtualserver_uptime'],
    $this->serverInfo['virtualserver_maxclients'],
    $this->serverInfo['virtualserver_reserved_slots'],
    $this->serverInfo['virtualserver_clientsonline'],
    $this->serverInfo['virtualserver_channelsonline'],
    $this->serverInfo['virtualserver_welcomemessage'],
    $this->serverInfo['virtualserver_name'],
    $this->serverInfo['virtualserver_client_connections'],
    $this->serverInfo['virtualserver_min_client_version'],
    date("m.d.y")
    );

    $sourceMessage = file_get_contents($file);

    $message = str_replace($varsMessage,$valuesMessage,$sourceMessage);

    return $message;
  }

  if($clid == null)
  {
    $this->serverInfo['virtualserver_uptime'] = $this->tsAdmin->convertSecondsToStrTime($this->serverInfo['virtualserver_uptime']);

    $varsMessage = Array(
    "{virtualserver_uptime}",
    "{virtualserver_maxclients}",
    "{virtualserver_reserved_slots}",
    "{virtualserver_clientsonline}",
    "{virtualserver_channelsonline}",
    "{virtualserver_welcomemessage}",
    "{virtualserver_name}",
    "{virtualserver_client_connections}",
    "{virtualserver_min_client_version}",
    "{date}"
    );

    $valuesMessage = Array(
    $this->serverInfo['virtualserver_uptime'],
    $this->serverInfo['virtualserver_maxclients'],
    $this->serverInfo['virtualserver_reserved_slots'],
    $this->serverInfo['virtualserver_clientsonline'],
    $this->serverInfo['virtualserver_channelsonline'],
    $this->serverInfo['virtualserver_welcomemessage'],
    $this->serverInfo['virtualserver_name'],
    $this->serverInfo['virtualserver_client_connections'],
    $this->serverInfo['virtualserver_min_client_version'],
    date("m.d.y")
    );

    //print_r($varsMessage);

    $sourceMessage = file_get_contents($this->config['files'][$file]);

    $message = str_replace($varsMessage,$valuesMessage,$sourceMessage);

    return $message;
  }
}









/** addError($name, $tsAdmin = false, $critical = flase)
  *
  * Wyświetla błąd w konsoli
  *
  * Parametry:
  * name - nazwa błędu
  * tsAdmin - wyświetlanie błędu ts3admin true/false
  * critical - zakończenie wykonywania skryptu true/false
  */
function addError($name, $tsAdmin = false, $critical = false) {
  // critical == true - Błąd krytyczny (kończy wykonywanie)
  // critical == false - Błąd umożliwiający dalsze wykoananie
  if(empty($name))  {
    print 'UNKNOWN ERROR'."\n";
  }

  if(is_bool($critical))  {
    if($critical)  {

      if($tsAdmin)  {
        $error = $this->tsAdmin->getDebugLog();
        print "CRITICAL ERROR: ". $name ."\n";
        print_r($error);
        die();
      }else {
        die("CRITICAL ERROR: ". $name ."\n");
      }
    }

    if(!$critical)  {
      if($tsAdmin)  {
        $error = $this->tsAdmin->getDebugLog();
        print "ERROR: ". $name ."\n";
        print_r($error."\n");
      } else {
        print 'ERROR: '.$name."\n";
      }
    }
  }
}









/** addInfo($name)
  *
  * Wyświetla informacje w konsoli
  *
  * Parametry:
  * name - nazwa informacji
  */
function addInfo($name) {
  print 'INFO: '. $name ."\n";
}










//*******************************************************************************************
//*********************************** Internal Functions ************************************
//*******************************************************************************************










/** setConfig()
  *
  * Ładuje konfiguracje do argumentu obiektu $config
  */
private function setConfig()  {
  $config = parse_ini_file($this->files['general-config'], true);

  if(empty($config))  {
    $this->addError($this->lang['config_file_loading_error'], false, true);
  }else {
    $this->addInfo($this->lang['config_file_loading_success']);
  }


  $permissionsLoad = parse_ini_file($this->files['permissions']);

  if(empty($permissionsLoad)) {
    $this->addError($this->lang['permissions_file_loading_error'], false, true);
  }else {
    $this->addInfo($this->lang['permissions_file_loading_success']);
  }

  $permissions = Array();

  foreach($permissionsLoad as $dbid => $perms) {
    $perms = preg_replace('/\s+/', '', $perms);
    $perm = explode(",", $perms);
    foreach($perm as $permTemp) {
      if(isset($permissions[$permTemp]) && !empty($permissions[$permTemp])) {
        $permissions[$permTemp] .= ",".$dbid;
      }else {
        $permissions[$permTemp] = $dbid;
      }
    }
  }

  $config['permissions'] = $permissions;


  //multibotConfig

  $functionList = parse_ini_file($this->files['function-list'], true);

  if(empty($functionList))  {
    $this->addError($this->lang['function-list_file_lading_error'], false, true);
  }else {
    $this->addInfo($this->lang['function-list_file_loading_success']);
  }

  foreach($functionList as $functionName => $functionFiles) {
    if(!isset($multibotConfig[$functionName]))  {
      $multibotConfig[$functionName] = parse_ini_file("Configs/Functions/". $functionFiles['config']);

      if(empty($multibotConfig[$functionName]) || !isset($multibotConfig[$functionName]))  { $this->addError($this->lang['file_loading_error']. $functionFiles['config']); }
    }else {
      $this->addError($this->lang['multibot_function_config_exist']. $functionName);
    }
  }

  $config['multibotConfig'] = $multibotConfig;


  $this->config = $config;
  return true;
}










/** refreshChannelList()
  *
  * Typ: Prywatna
  *
  * Ponownie ładuje listę kanałów do zmiennej $channelList
  *
  */
private function refreshChannelList()
{
  $channelList = $this->tsAdmin->channelList(); // Przypisanie tablice z listą kanałów do parametru $channelList
  if($channelList['success'] && !empty($channelList['data'])) {
    $this->channelList = $channelList['data'];
    return true;
  }else {
    $this->addError($this->lang['refresh_channellist_error']);
    return false;
  }

}










/** refreshClientList()
  *
  * Typ: Prywatna
  *
  * Ponownie ładuje listę użytkowników do zmiennej $clientList
  *
  */
private function refreshClientList($query = true)
{
  $clientList = $this->tsAdmin->clientList("-uid -away -voice -times -groups -info -icon -country -ip -badges"); // Przypisuje tablice z listą użytkowników do parametru $clientList
  if($clientList['success'] && !empty($clientList['data'])) {
    $this->clientList = $clientList['data'];
    return true;
  }else {
    $this->addError($this->lang['refresh_clientlist_error']);
    return false;
  }

}







/** refreshServerInfo()
  *
  * Typ: Prywatna
  *
  * Ponownie ładuje informacje o serwerze do zmiennej $serverInfo
  *
  */
private function refreshServerInfo()
{
  if(empty($this->clientList)) {$this->refreshClientList();}

  $serverInfo = $this->tsAdmin->serverInfo();

  if($serverInfo['success'] && !empty($serverInfo['data']))  {
    foreach($this->clientList as $value => $temp) {
      if($temp['client_type'] != 1) {
        $clientList[] = "client";
      }
    }
    if(!isset($clientList))  {
      return false;
    }

    $serverInfo['data']['virtualserver_clientsonline'] = count($clientList);
    $this->serverInfo = $serverInfo['data'];
    return true;
  }else {
    $this->addError($this->lang['refresh_serverinfo_error']);
    return false;
  }
}










/** refreshServerGroupNames()
  *
  * Typ: Prywatna
  *
  * Przypisuje do zmiennej $serverGroupNames nazwy grup
  *
  */
private function refreshServerGroupNames()
{

  $groupsList = $this->tsAdmin->serverGroupList();

  foreach($groupsList['data'] as $groupsListTemp)
  {
    if($groupsListTemp['type'] == 2)  {
      $this->serverGroupNames['query'][$groupsListTemp['sgid']] = Array("name" => $groupsListTemp['name'], "iconid" => $groupsListTemp['iconid'], "savedb" => $groupsListTemp['savedb']);
    }
    else {
      $this->serverGroupNames['regular'][$groupsListTemp['sgid']] = Array("name" => $groupsListTemp['name'], "iconid" => $groupsListTemp['iconid'], "savedb" => $groupsListTemp['savedb']);
    }
  }
}










/** __construct($config)
  *
  * $config - ścieżka do pliku konfiguracyjnego
  *
  * Typ: Konstruktor
  *
  * Konstruktor tworzy referencjie do ts3admin oraz konfiguruje połączenie
  *
  */
function __construct()
{
  require($this->files['ts3admin']);

  $lang = parse_ini_file($this->files['general-config'], true);

  if(empty($lang))  {
    $this->addError("Can't load \"Configs/general-config.ini\" \n", false, true);
  }

  $this->lang = parse_ini_file($this->langFiles[$lang['general_config']['lang']]);

  if(empty($this->lang))  {
    $this->addError("Can't load \"Locales/". $this->langFiles[$lang['general_config']['lang']] ."\n", false, true);
  }else {
    unset($lang);
  }

  $this->setConfig();

  $this->tsAdmin = new ts3Admin($this->config['general_config']['adress'], $this->config['general_config']['query_port']);

  if(!is_object($this->tsAdmin))  {
    $this->addError($this->lang['ts3admin_create_error'], false, true);
  }else{
    $this->addInfo($this->lang['ts3admin_create_success']);
  }

  if(!$this->tsAdmin->getElement('success', $this->tsAdmin->connect()))  {
    $this->addError($this->lang['ts3server_connect_error'], true, true);
  }else{
    $this->addInfo($this->lang['ts3server_connect_success']);
  }

  if(!$this->tsAdmin->getElement('success', $this->tsAdmin->login($this->config['general_config']['login'], $this->config['general_config']['password'])))  {
    $this->addError($this->lang['ts3server_login_error'], true, true);
  }else {
    $this->addInfo($this->lang['ts3server_login_success']);
  }

  if(!$this->tsAdmin->getElement('success', $this->tsAdmin->selectServer($this->config['general_config']['server_port'])))  {
    $this->addError($this->lang['ts3server_select_error'], true, true);
  }else {
    $this->addInfo($this->lang['ts3server_select_success']);
  }
}


// Koniec klasy
}


$functionList = parse_ini_file("Configs/function-list.ini", true);

foreach($functionList as $functionName => $value)  {
  require("Functions/".$value['function']);
  print 'FUNCTION LOADED: ' . $functionName . "\n";
}


?>
