<?php
/** Referencja do tsAdmin: $this->tsAdmin
  *
  * Kod całego pliku wykonywany jest w przypadku użycia komendy o nazwie pliku.
  *
  * Tablica commandInfo
  * (
  *   [command] => "Nazwa użytej komendy" Array(0 => "Pierwsy_człon", 1 => "Drugi_człon" ...)
  *   [clientId] => "Id użytkownika który wysłał wiadomość"
  *   [clientUID] => "UID użytkownika który wysłał wiadomość"
  *   [clientName] => "Nick użytkownika który wysłał wiadomość"
  * )
  *
  * Dostępne funkcje:
  * - Pamiętaj! Pred każdą funkcją musi być operator $this-> np. $this->getInstanceId("clock")
  *
  * - getInstanceId($function) - Zwraca id instancji w której uruchomiona jest dana funkcja
  * - killInstance($id) - Zabija instancje o podanym id
  * - sendToInstance($id, $msg) - Wysyła wiadomość do instancji
  * - instanceRead($id) - Odczytuje informacje z instancji
  * - getConfig() - Zwraca tablice z konfiguracją
  * - getCommandList() - Zwraca tablice z listą wczytanych komend
  * - refreshPermissionList() - Odświerza listę permisji
  * - refreshCommandList() - Odświerza listę komend
  * - refreshMultibotConfig() - Odświrza listę komend multibota
  * - getMultibotConfig() - Zwraca konfiguracje multibota
  */

if(count($commandInfo['command']) == 2) {
  $command = $commandInfo['command'][0]." ".$commandInfo['command'][1];
  $id = $this->getInstanceId($commandInfo['command'][1]);
  $status = $this->sendToInstance($id, $command);
  $r = $this->readFromInstance($id);

  if(!$status) {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
  }elseif($r == 'run') {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_status_function_run']);
  }elseif($r == 'stop') {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_status_function_stop']);
  }elseif($r == 'badfunction')  {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_function']);
  }else {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
  }
}elseif(count($commandInfo['command']) < 2) {
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_status_bad_argument']);
}elseif(count($commandInfo['command']) > 2) {
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_too_many_arguments']);
}else {
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
}
?>
