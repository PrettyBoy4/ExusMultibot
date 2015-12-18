<?php
/**
 *                         commands-core.class.php
 *                         ------------------
 *   Utworzony             : 27. Listopad 2015
 *   Prawa Autorskie       : Karol Krupa (Exus)
 *   E-mail                : karo2krupa@gmail.com
 *   Wersja                : 1.0.0 Alpha
 *   Ostatnia modyfikacja  : 18. Grudzień 2015
 *
 *
 *  Plik ten jest podstawą całego multibota
 *
 *  Kod zawarty w tym pliku odpowiada za podstawowe zadania całego bota
 *  - Inicjuje instancje
 *  - Kontroluje instancje
 *  - Uruchamia/wyłącza poszczególne funkcje i instancje
 *  - Odowiada za obsługę komend serwera
 *
 *  Instancje serwera tworzone są w sposób dynamiczny tzn. na wybraną ilość instancji w konfiguracji
 *  równomiernie rzkładane są wybrane funkcje tak aby zapwnić jak najszybsze i najbardziej optymalne działanie
 *  całego bota, ponadto kod zawarty w tym pliku odpowiada za obsługę komend dzięki czemu komendy obsługiwane są
 *  ze znikomym opóźnieniem w trybie Half-Duplex. Jednakże rdzeń jest na tyle szybki że prawdopodobnie opóźnienia
 *  te nie będą widoczne. Obsługa komend w trybie Half-Duplex skutkuje możliwością wykonywania jednej komendy na raz
 *  przez co jeżeli jeden administrator aktualnie zlecił wykonanie polecenia w tym momencie żaden inny administrator
 *  nie będzie mógł wykonać polecenia. Zjawisko przy dużych szęstotliwościach odświerzania (Małym czasie odświerzania)
 *  nie powinno być widoczne.
 *
 */






require("Core/commands-core.class.php");
/** getSmallerIndex($table)
  *
  * Funkca pomocniczna do tworzenia instancji.
  * W argumencie przyjmuje tablicę z instancjami a w rezultacie oddaje id instancji która ma najmniejszą wagę.
  */
function getSmallerIndex($table)  {
  $index = 0;
  for($i = 1; $i <= (count($table)-1); $i++) {
    if(!($table[$index]['weight'] <= $table[$i]['weight']))  {
        $index = $i;
    }
  }
  return $index;
}





function getBigestIndex($table) {
  $index = 0;
  for($i = 1; $i <= (count($table)-1); $i++) {
    if(!($table[$index]['weight'] >= $table[$i]['weight']))  {
        $index = $i;
    }
  }
  return $index;
}




/** sendCommand($command)
  *
  * Funkcja wysyłająca komendy do serwera ts3 poprzez socket ts3admin
  */
function sendCommand($command)  {
  global $commands;

  $socket = $commands->getSocket();

  $splittedCommand = str_split($command, 1024);
  $splittedCommand[(count($splittedCommand) - 1)] .= "\n";
  foreach($splittedCommand as $commandPart) {
    fputs($socket, $commandPart);
  }
  return fgets($socket, 4096);
}




/** unEscapeText($text)
  *
  * Funkcja zamieniająca znaczniki w ciągach
  * Zapożyczona i zmodyffikowana z ts3admin
  */
function unEscapeText($text) {
  $escapedChars = array("\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/");
  $unEscapedChars = array('', '', '', '', '', ' ', '|', '/');
  $text = str_replace($escapedChars, $unEscapedChars, $text);
  return $text;
}




/** getData()
  *
  * Pobiera dane z socketu ts3admin
  * Zapożyczona i zmodyfikowana z ts3admin
  */
function getData()  {

  global $commands;

  $socket = $commands->getSocket();

  $data = fgets($socket, 4096);

  if(!empty($data)) {
    $datasets = explode(' ', $data);

    $output = array();

    foreach($datasets as $dataset) {
      $dataset = explode('=', $dataset);

      if(count($dataset) > 2) {
        for($i = 2; $i < count($dataset); $i++) {
          $dataset[1] .= '='.$dataset[$i];
        }
        $output[unEscapeText($dataset[0])] = unEscapeText($dataset[1]);
      }else{
        if(count($dataset) == 1) {
          $output[unEscapeText($dataset[0])] = '';
        }else{
          $output[unEscapeText($dataset[0])] = unEscapeText($dataset[1]);
        }
      }
    }
    return $output;
  }
}




// Tworzy obiekt commandsCore
$commands = new commandsCore();

// Wczytuje język
$lang = $commands->getLang();

// Wczytuje konfigurację commandsCore oraz multibota
$config = $commands->getConfig();
$multibotCFG = $commands->getMultibotConfig();


// type:
// dbid
// uid
function checkUserIsOnline($type, $id) {
  global $commands;
  $tsAdmin = $commands->tsAdmin;

  if($type == "uid")  {
    $status = $tsAdmin->clientGetIds($id);
    if(!empty($status['data'][0]['clid']))  {
      return true;
    }else {
      return false;
    }
    return false;
  }elseif($type == "dbid")  {
    $status = $tsAdmin->clientGetNameFromDbid($id);
    $status = $tsAdmin->clientGetIds($status['data']['cluid']);
    if(!empty($status['data'][0]['clid']))  {
      return true;
    }else {
      return false;
    }
    return false;
  }
}


function instanceCount()  {
  global $multibotCFG;
  global $config;
  global $commands;
  global $instanceList;

  $functionCount = 0;
  foreach($multibotCFG as $name => $temp)  {
    if(isset($temp['enable']))  {
      if((!empty($temp['enable'])) && ($temp['enable'] == true)) {
        $functionCount++;
      }
    }
  }

  if($functionCount == 0) {
    $commands->addError("Nie włączono żadnych funckji", false, true);
    return false;
  }
  if($config['general_config']['instances'] >= $functionCount)  {

    for($i = 1; $i <= $functionCount; $i++)  {
      $instanceList[] = Array('functions' => Array(), 'weight' => 0);
    }

    return $functionCount;
  }elseif($config['general_config']['instances'] > 0) {

    for($i = 1; $i <= $config['general_config']['instances']; $i++)  {
      $instanceList[] = Array('functions' => Array(), 'weight' => 0);
    }

    return $config['general_config']['instances'];
  }else {
    $commands->addError("Nie wybrano ilości instancji", false, true);
    return false;
  }
}


$instanceList = Array();

instanceCount();

foreach($multibotCFG as $name => $vars) {
  if(!isset($vars['weight'])) {
    $vars['weight'] = 1;
  }

  if($vars['enable'] && $vars['primary_instance'])  {
    $instanceList[0]['functions'][] = $name;
    $instanceList[0]['weight'] += $vars['weight'];
    if($config['general_config']['protect_primary_instance']) {
      $instanceList[0]['weight'] += 1000;
    }
  }elseif($vars['enable'])  {
    $index = getSmallerIndex($instanceList);
    $instanceList[$index]['functions'][] = $name;
    $instanceList[$index]['weight'] += $vars['weight'];
  }
}


foreach($instanceList as $instances)  {
  foreach($instances['functions'] as $functionName)  {
    if(isset($functions) && !empty($functions)) {
      $functions .= ",". $functionName;
    }else{
      $functions = $functionName;
    }
  }
  $commands->createInstance($functions, $instances['weight']);
  unset($functions);
}




// Pętla sprawdzająca wpisywane dane
$timer = date('r', time() + 120);
sendCommand("servernotifyregister event=textserver");
sendCommand("servernotifyregister event=textprivate");
while(true) {
  $r = getData();

  if(is_array($r) && !empty($r))  {
    if(array_key_exists("notifytextmessage", $r)) {
      if(($r['targetmode'] == 3) && ("!" == substr($r['msg'], 0, 1))) {
        $r['msg'] = str_replace("!", "", $r['msg']);
        sendCommand("servernotifyunregister");
        $commands->executeCommand($r, true);
        sendCommand("servernotifyregister event=textserver");
        sendCommand("servernotifyregister event=textprivate");
      }elseif ($r['targetmode'] == 1) {
        sendCommand("servernotifyunregister");
        if("!" == substr($r['msg'], 0, 1))  {
          $r['msg'] = str_replace("!", "", $r['msg']);
        }
        $status = $commands->executeCommand($r);
        if($status == "1")  {
          $commands->tsAdmin->sendMessage(1,$r['invokerid'], $lang['command_does_not_exist']);
        }elseif ($status == "2") {
          $commands->tsAdmin->sendMessage(1,$r['invokerid'], $lang['command_no_permission']);
        }elseif ($status == "3") {
          $commands->tsAdmin->sendMessage(1,$r['invokerid'], $lang['command_permission_find_error']);
        }
        sendCommand("servernotifyregister event=textserver");
        sendCommand("servernotifyregister event=textprivate");
      }
    }
  }
  //Sprawdza kim jest aby achować połączenie z serwerem
  if($timer < date('r'))  {
    $commands->tsAdmin->whoAmI();
    $timer = date('r', time() + 120);
  }
  sleep(1);
}
?>
