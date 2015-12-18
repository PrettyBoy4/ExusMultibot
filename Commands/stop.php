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
if((count($commandInfo['command']) == 2) && ($commandInfo['command'][0] == "stop")) {
  if(isset($this->config['multibotConfig'][$commandInfo['command'][1]]))  {
    $id = $this->getInstanceId($commandInfo['command'][1]);
    if($id !== false)  {
      $sendStatus = $this->sendToInstance($id, "status ".$commandInfo['command'][1]);
      $functionStatus = $this->readFromInstance($id);
      if(!$sendStatus)  {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
      }elseif($functionStatus == "stop") {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_stop_function_stopped']);
      }elseif($functionStatus == "run")  {
        if((!$this->sendToInstance($id, "stop ".$commandInfo['command'][1])) && (!$this->sendToInstance($id, "status ".$commandInfo['command'][1]))) {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
        }else {
          if(!$functionStatus = $this->readFromInstance($id)) {
            $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
          }elseif($functionStatus == "stop") {
            $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_stop_function_stop']);
          }else {
            $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
          }
        }
      }elseif($functionStatus == "badfunction") {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_function']);
      }elseif(!$functionStatus) {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_read_error'].$id);
      }
    }else {
      $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_stop_function_stopped']);
    }
  }else {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_function']);
  }
}
?>
