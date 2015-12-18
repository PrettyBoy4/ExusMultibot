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
if($commandInfo['command'][0] == "reload")  {
  if($this->setConfig())  {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_success']."Command-Core (Config)");
  }else {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_error']."Command-Core (Config)");
  }

  if($this->setCommandList())  {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_success']."Command-Core (Function List)");
  }else {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_error']."Command-Core (Function List)");
  }

  $return = Array();
  foreach($this->instanceList['instances'] as $id => $value) {
  $this->sendToInstance($id, "reloadconfig");
  $return[$id] = $this->readFromInstance($id);
  }

  if(!empty($return)) {
    foreach($return as $id => $value) {
      if($value == "success") {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_success'].$id);
      }elseif($value == "error")  {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_reload_error'].$id);
      }else {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
      }
    }
  }
}

?>
