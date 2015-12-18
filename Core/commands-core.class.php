<?php
/**
 *                         commands-core.class.php
 *                         ------------------
 *   Utworzony             : 27. Listopad 2015
 *   Prawa Autorskie       : Karol Krupa (Exus)
 *   E-mail                : karo2krupa@gmail.com
 *   Wersja                : 2.0.0 Alpha
 *   Ostatnia modyfikacja  : 18. Grudzień 2015
 *
 *
 *  Plik ten zawira kod całej klasy kontrolera komend który jest jednocześnie
 *  kontrolerem całego multibota. Klasa ta udostępnia podstawowe funkcje do
 *  zarządzania, tworzenie, usuwania oraz modyfikowania działania instancji
 *  multibota.
 *
 */

/**
 * @author      Karol Krupa (Exus)
 * @version     1.0.0
 * @copyright   Copyright (c) 2015, Karol K.
 * @package		  ExusMultibot
 * @link        http://exus.ovh
 */
class commandsCore {


//*******************************************************************************************
//****************************************** Vars *******************************************
//*******************************************************************************************

  private $files = Array('ts3admin' => 'Core/ts3admin.class.php', 'general-config' => 'Configs/general-config.ini', 'permissions' => 'Configs/permissions.ini', 'function-list' => 'Configs/function-list.ini', 'commands-dir' => 'Commands/', 'multibot-core' => 'Core/multibot-core.php', 'commands-functions' => 'Core/commands-functions.php');

  private $langFiles = Array('pl' => 'Locales/pl.ini');
  private $lang;

  private $config;

  public $instanceList; //Array('id' = 0, 'instance_sockets' => Array(), instances = Array());

  public $tsAdmin;

  private $commandList;

  private $socketFile = "Core/socketInternal.sock";

  //public $startedFunctions;

//*******************************************************************************************
//************************************ Public Functions *************************************
//******************************************************************************************










/** getLang()
  *
  * Zwraca plik językowy
  */
function getLang()  {
  return $this->lang;
}










/**getSocket()
  *
  * Zwraca socket ts3admin
  */
function getSocket() {
  return $this->tsAdmin->runtime['socket'];
}










/** getConfig
  *
  * Zwraca aktualnie wczytaną konfiguracje
  */
function getConfig()  {
  return $this->config;
}










/** getMutlibotConfig()
  * zwraca konfiguracje multibota
  */
function getMultibotConfig() {
  return $this->config['multibotConfig'];
}









/** getCommandList()
  *
  * Zwraca liste wczytanych komend
  */
function getCommandList() {
  return $this->commandList;
}









/** excuteCommand($return)
  *
  * Wykonuje daną komendę
  *
  * Argumenty:
  * - return - tablica z inforamcjami zwrotnymi z query.
  */
public function executeCommand($return) {

  if($this->commandList == false) {
    return 4;
  }
  $commandInfo = Array('command' => explode(" ", $return['msg']), 'clientId' => $return['invokerid'], 'clientUID' => $return['invokeruid'], 'clientName' => $return['invokername']);
  $tsAdmin = $this->tsAdmin;
  if(isset($this->commandList[$commandInfo['command'][0]]))  {

    $dbid = $this->tsAdmin->clientGetDbIdFromUid($commandInfo['clientUID']);

    if(!$dbid['success'])  {return false;}

    $clientGroups = $this->tsAdmin->serverGroupsByClientID($dbid['data']['cldbid']);

    if(!$clientGroups['success']) {return false;}

    if(!($groups = $this->config['permissions']["c_permission_".$commandInfo['command'][0]])) {
      $this->addError($this->lang['permission_find_error'] . " c_permission_".$commandInfo['command'][0]);
      return 3;
    }

    if(strstr($groups, "all") !== false)  {
      include($this->files['commands-dir'] . $this->commandList[$commandInfo['command'][0]]);
      return 0;
    }

    foreach($clientGroups['data'] as $group) {
      if(strstr($groups, $group['sgid'])) {

        include($this->files['commands-dir'] . $this->commandList[$commandInfo['command'][0]]);

        return 0;
      }
    }
    return 2;
  }else {
    return 1;
  }
}









/** createInstance()
  *
  * Tworzy nową instancje multibota
  */
function createInstance($functions, $weight)  {

  if(empty($this->instanceList))  {
    $this->instanceList['id'] = 0;
  }else {
    $this->instanceList['id']++;
  }

  if(empty($functions)) {
    return $this->addError($this->lang['instance_function_list_error'], false, true);
  }

  print "\n";
  $this->addInfo($this->lang['instance_launching'] . $this->instanceList['id']);

  $result = shell_exec("screen -dmS ExusMultibotInstance php " . $this->files['multibot-core']);

  if($result == "[screen is terminated]") {
    $this->addError($this->lang['instance_launch_error'] . $this->instanceList['id'], false, true);
  }elseif($result != ''){
    $this->addError($this->lang['instance_launch_error1'] . $result, false, true);
  }else {
    $this->addInfo($this->lang['instance_launch_success'] . $this->instanceList['id']);
  }

  $this->addInfo($this->lang['waiting_for_instance_connect'] . $this->instanceList['id']);
  socket_listen($this->socket_instance);

  if($this->instanceList['instance_sockets'][$this->instanceList['id']] = socket_accept($this->socket_instance)) {

    $this->addInfo($this->lang['instance_connect_success']);

    if($this->instanceList['id'] == 0)  {
      $msg = $this->config['instance_info']['instance_name'] . "," . $functions;
    }else {
      $msg = $this->config['instance_info']['instance_name'] . $this->instanceList['id'] . "," . $functions;
    }

    if(!socket_write($this->instanceList['instance_sockets'][$this->instanceList['id']], $msg, strlen($msg)))
    {
      $this->addError($this->lang['instance_send_instructions_error'], false, true);
    }else {
      $this->addInfo($this->lang['instance_send_instructions_success']);
    }
    $this->addInfo($this->lang['waiting_for_response_from_instance']);

    sleep(3);

    if($buffer = socket_read($this->instanceList['instance_sockets'][$this->instanceList['id']], 2048))  {
      $this->addInfo($this->lang['response_from_instance_success']);
      $this->addInfo($this->lang['instance_informations'] . "\n");
      $runingFunctions = explode(',', $buffer);
      $c = count($runingFunctions);
      $c--;
      unset($runingFunctions[$c]);
      $nick = $runingFunctions[0];
      unset($runingFunctions[0]);
      //print_r(Array('id' => $this->instanceList['id'], 'Process Name' => $this->config['instance_info']['name'].$this->instanceList['id'], 'Bot Name' => $nick, 'Functions' => $runingFunctions));
      print_r(Array('id' => $this->instanceList['id'], 'Process Name' => 'ExusMultibotInstance', 'Bot Name' => $nick, 'Functions' => $runingFunctions));
      print "\n";

      //$this->instanceList['instances'][$this->instanceList['id']] = Array('process' => $this->config['instance_info']['name'].$this->instanceList['id'], 'bot_name' => $nick, 'functions' => $runingFunctions);
      $this->instanceList['instances'][$this->instanceList['id']] = Array('process' => "ExusMultibotInstance", 'bot_name' => $nick, 'functions' => $runingFunctions, 'weight' => $weight);
      return $this->instanceList['id'];
    }
  }else {
    shell_exec("screen -XS ". $this->config['instance_info']['name'].$this->instanceList['id'] ."quit");
    $this->addError("Nie udało połączyć się z instancją. Instancja zostanie wyłączona.", true);
  }
}










/** getInstanceId($function)
  *
  * Zwraca id instancji w której uruchomiona jest podana funkcja
  */
function getInstanceId($function)  {
  foreach($this->instanceList['instances'] as $value => $instanceInfo) {
    if(in_array($function, $instanceInfo['functions']))  {
      return $value;
    }
  }
  return false;
}










/** sendToInstance($id, $msg)
  *
  * Wysyła polecenie do instancji o id $id
  */
function sendToInstance($id, $msg)  {
  $instance = $this->instanceList['instance_sockets'][$id];

  if(!socket_write($instance, $msg, strlen($msg)))
  {
    return false;
  }else {
    return true;
  }
}









/** readFromInstance($id)
  *
  * Odczytuje informacje otrzymane od instancji
  */
public function readFromInstance($id)  {
  $instance = $this->instanceList['instance_sockets'][$id];

  sleep(1);

  if(!($buffer = socket_read($instance, 2048))) {
    return false;
  }else {
    return $buffer;
  }
}









/** killInstance($id)
  *
  * Wyłącza daną instancje
  */
public function killInstance($id)  {
  if(isset($this->instanceList['instances'][$id]))  {
    $return = shell_exec("screen -XS ". $this->instanceList['instances'][$id]['process'] ." quit");
  }else {
    return false;
  }

  if($return == "No screen session found.") {
    $this->addError($this->lang['instance_kill_error']. $this->config['instances'][$id]['process']);
    return false;
  }elseif ($return = "") {
    return true;
  }else {
    return false;
  }
}









/** killAllInstances()
  *
  * Wyłącza wszystkie instancje
  */
function killAllInstances() {
  foreach($this->instanceList['instances'] as $id => $info)  {
    $this->killInstance($id);
  }
  return true;
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
      if(file_exists($this->socketFile))  {
        unlink($this->socketFile);
      }

      $this->killAllInstances();

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










/** setCommandList()
  *
  * Tworzy listę komend
  */
private function setCommandList()  {

  if(is_dir($this->files['commands-dir']))  {
    if($dh = opendir($this->files['commands-dir'])) {
      while(($file = readdir($dh)) !== false) {
        if(strstr($file, ".php") !== false) {
          print_r($file);
          $command_name = substr($file, 0, strpos($file, ".php"));
          $return[$command_name] = $file;
        }
      }
      if(empty($return))  {
        $this->addInfo($this->lang['command_list_load_error']);
        $this->commandList  = false;
        return false;
      } else {
        $this->commandList = $return;
        $this->addInfo($this->lang['command_list_load_success']);
        return true;
      }
      closedir($dh);
    }else {
      $this->addError($this->lang['open_commands_folder_error']);
      $this->commandList = false;
      return false;
    }
  }else {
    $this->addError($this->lang['open_commands_folder_error']);
    $this->commandList = false;
    return false;
  }
}










/** __construct()
  *
  * Wykonuje podstawowe operacje do działania
  */
function __construct()  {

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

  if(!$this->tsAdmin->getElement('success', $this->tsAdmin->setName($this->config['general_config']['bot_name'])))  {
    $this->addError($this->lang['set_bot_name_error'], ture, false);
  }else {
    $this->addInfo($this->lang['set_bot_name_success']);
  }

  $this->socket_instance = socket_create(AF_INET, SOCK_STREAM, 0);
  if(!$this->socket_instance)  {
    $this->addError("/n".$this->lang['internal_socket_create_error'], false, true);
  }else {
    $this->addInfo($this->lang['internal_socket_create_success']);
  }

  if(!socket_bind($this->socket_instance, 'localhost', 12345)) {
    $this->addError($this->lang['internal_socket_bind_error']);
    unlink($this->socketFile);
    if(!socket_bind($this->socket_instance, $this->socketFile)) {
      $this->addError($this->lang['internal_socket_bind_error'], false, true);
    }else {
      $this->addInfo($this->lang['internal_socket_bind_success']);
    }
  }else {
    $this->addInfo($this->lang['internal_socket_bind_success']);
  }

  $this->setCommandList();
}










// Konec Klasy
}
