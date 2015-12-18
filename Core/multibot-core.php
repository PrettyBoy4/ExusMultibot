<?php
/**
 *                         multibot-core.php
 *                         ------------------
 *   Początek projektu      : 10. Listopad 2015
 *   Prawa Autorskie        : Karol Krupa (Exus)
 *   E-mail                 : karo2krupa@gmail.com
 *   Wersja                 : 1.0.0 Alpha
 *   Ostatnia modyfikacja   : 18. Grudzień 2015
 *
 *
 *  Plik ten zawiera kontoroler multibota (Pierwsza wesja) wzbogaacony o
 *  obsługę socketu łączącego go bezpośredznio z kontrolerem całego multiota (v2)
 *  od tego momentu kontroler ten jest zależny od konrrolera całego bota ponieważ
 *  sam nie potrafi wyznaczyć jakie funkcje ma uruchomić.
 *
 *  Zasada działania.
 *  Kontorler całego multibota (commands-core.php) uruchamia proces multibota
 *  (multibot-core.php) następnie przesyła nazwy funkcji które mają być w nim
 *  uruchomione. Kontroler pojedyńczego procesu (Ten plik) uruchamia dane funkcje
 *  i następnie wysyła poprzez socket do głownego kontrolera (commands-core.php)
 *  nazwy fukncji które miał uruchomić co jest równoznaczne z potwierdzeniem odebrania
 *  poprawnych danych.
 *
 */
date_default_timezone_set('Europe/Berlin');

require("Core/multibot-core.class.php");

$multibotCore = new multibotCore();

$lang = $multibotCore->getLang();

$vars = Array('clock' => Array(), 'pokeBot' => Array('clientList', 'channelList'), 'adminStatus' => Array('serverGroupNames'), 'afkAutoMove' => Array('clientList'), 'welcomeMessage' => Array('clientList', 'serverInfo'), 'adminChannelStatus' => Array('clientList'), 'channelChecker' => Array('clientList', 'serverInfo', 'channelList') );


//socket

if(!$socket = socket_create(AF_INET, SOCK_STREAM, 0)) {
  $multibotCore->addError($lang['internal_socket_create_error'], false, true);
}else {
  $multibotCore->addInfo($lang['internal_socket_create_success']);
}

sleep(5);


if(!socket_connect($socket, 'localhost', 12345)) {
  $multibotCore->addError($lang['internal_socket_connect_error'], false, true);
}else {
  $multibotCore->addInfo($lang['internal_socket_connect_success']);
}

if(!socket_set_nonblock($socket)) {
  $multibotCore->addError($lang['internal_socket_bind_error'], false, true);
}else {
  $multibotCore->addInfo($lang['internal_socket_bind_success']);
}

$timeSocket = date('r', time() + 10);

while(true) {
  sleep(1);

  if(!$buffer = socket_read($socket, 2048)) {
    $multibotCore->addError($lang['internal_socket_read_error'], false, true);
  }else {
    $multibotCore->addInfo($lang['internal_socket_read_success']);
  }

  if(!empty($buffer)) {
    $buffer = preg_replace('/\s+/', '', $buffer);
    $functions = explode(",", $buffer);

    $multibotCore->setName($functions[0]);

    $nick = $functions[0];
    unset($functions[0]);

    foreach($multibotCore->config['multibotConfig'] as $value => $temp) {
      if(isset($temp['enable']))  {
        if(in_array($value, $functions))  {
          $multibotCore->config['multibotConfig'][$value]['enable'] = true;
          $startedFunctions[] = $value;
        }else {
          $multibotCore->config['multibotConfig'][$value]['enable'] = false;
        }
      }
    }
    $return = $nick.",";
    foreach($multibotCore->config['multibotConfig'] as $value => $temp) {
      if(isset($temp['enable']))  {
        if($temp['enable'] == true) {
          $return .= $value.',';
          $functionToStart[] = $value;
        }
      }
    }
    print "\n";
    print $lang['functions_to_start'] . "\n";
    print_r($functionToStart);
    print "\n";
    socket_write($socket, $return, strlen($return));
    break;
  }
  if($timeSocket <= date('r'))  {
    $multibotCore->addError($lang['response_from_commandcore_error'], false, true);
    die();
  }
}












function refreshVarsList() {
  global $multibotCore;
  global $vars;
  global $varsList;
  foreach($multibotCore->config['multibotConfig'] as $value => $temp) {
    if(isset($temp['enable']))
    if($temp['enable']) {

      if(!empty($vars[$value]))

      foreach($vars[$value] as $valueTemp)  {
        if(isset($varsList[$valueTemp])) {
          if($varsList[$valueTemp] > $temp['refresh']) {
            $varsList[$valueTemp] = $temp['refresh'];
            continue;
          }
          continue;
        }
        $varsList[$valueTemp] = $temp['refresh'];
      }
    }
  }
}












function socketSend($msg) {
  global $socket;
  if(socket_write($socket, $msg, strlen($msg)) !== false) {
    return true;
  }elseif(socket_write($socket, $msg, strlen($msg)) !== false) {
    return true;
  }else {
    return false;
  }
}












function socketWrite()  {
  global $socket;
  return socket_read($socket, 4096);
}












function command_status()  {
  global $buffer;
  global $startedFunctions;
  global $multibotCore;
  global $socket;
  if(($buffer[0] == "status") && !empty($buffer[1]))  {
    if($multibotCore->config['multibotConfig'][$buffer[1]]['enable'] == true) {
      $msg = "run";
      socketSend($msg);
      //socket_write($socket, $msg, strlen($msg));
      return true;
    }elseif ($multibotCore->config['multibotConfig'][$buffer[1]]['enable'] == false) {
      $msg = "stop";
      socketSend($msg);
      //socket_write($socket, $msg, strlen($msg));
      return true;
    }else {
      $msg = "badfunction";
      socketSend($msg);
      //socket_write($socket, $msg, strlen($msg));
      return true;
    }
  }else {
    socketSend("missing arg1");
    return false;
  }
}












function command_start()  {
  global $buffer;
  global $startedFunctions;
  global $multibotCore;
  global $socket;
  if(($buffer[0] == 'start') && !empty($buffer[1])) {
    if($multibotCore->config['multibotConfig'][$buffer[1]]['enable'] == true) {
      $msg = "runing";
      socketSend($msg);
    }elseif($multibotCore->config['multibotConfig'][$buffer[1]]['enable'] !== true) {
      $multibotCore->config['multibotConfig'][$buffer[1]]['enable'] = true;
      $varName = $buffer[1];
      global $$varName;
      $$varName = new $buffer[1]($multibotCore);
      refreshVarsList();
      $msg = "started";
      socketSend($msg);
    }else {
      $msg = "badfunction";
      socketSend($msg);
    }
  }
}











function command_stop()  {
  global $buffer;
  global $startedFunctions;
  global $multibotCore;
  global $socket;
  if(($buffer[0] == 'stop') && !empty($buffer[1])) {
    if($multibotCore->config['multibotConfig'][$buffer[1]]['enable'] == true) {
      $multibotCore->config['multibotConfig'][$buffer[1]]['enable'] = false;
      $msg = "stop";
      socketSend($msg);
      refreshVarsList();
      return true;
    }else {
      $msg = "stoped";
      socketSend($msg);
      return true;
    }
  }
}











function command_reloadconfig() {
  global $multibotCore;
  global $buffer;
  global $break;
  print 'reload';
  $status = $multibotCore->refreshConfig();
  if($status) {
    socketSend("success");
    $break = true;
    return true;
  }else {
    socketSend("error");
    return false;
  }
}











function commands() {
  global $socket;
  global $multibotCore;
  global $buffer;
  global $startedFunctions;
  $commandList = Array('status', 'start', 'stop', 'reloadconfig');
  if(in_array($buffer[0], $commandList))  {
    $command = "command_".$buffer[0];
    $return = $command();
    unset($buffer);
    return $return;
  }else {
    $msg = "badfunction";
    socket_write($socket, $msg, strlen($msg));
    return false;
  }
}





//Kontroler
while(true)
{
$break = false;

$varsList = Array();

foreach($multibotCore->config['multibotConfig'] as $value => $temp) {
  if(isset($temp['enable']))
  if($temp['enable']) {
    $$value = new $value($multibotCore);

    if(!empty($vars[$value]))

    foreach($vars[$value] as $valueTemp)  {
      if(isset($varsList[$valueTemp])) {
        if($varsList[$valueTemp] > $temp['refresh']) {
          $varsList[$valueTemp] = $temp['refresh'];
          continue;
        }
        continue;
      }
      if(isset($temp['refresh'])) {
        $varsList[$valueTemp] = $temp['refresh'];
      }else {
        $multibotCore->addError("Brak wartości refresh w konfiguracji funkcji ".$value, false, true);
      }

    }
  }
}



while(true) {
  sleep(1);
  $buffer = socketWrite();

  if(!empty($buffer)) {
    $buffer = explode(" ", $buffer);
    commands();
    if($break) {
      break;
    }
  }

  foreach($multibotCore->config['multibotConfig'] as $value => $temp) {
    if(isset($temp['enable']))
    if($temp['enable']) {
      foreach($varsList as $varsListValue => $varsListTemp)  {
        $multibotCore->refresh($varsListValue, $varsListTemp);
      }
      $$value->start($temp['refresh']);
    }
  }
}


}
?>
