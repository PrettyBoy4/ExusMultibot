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
  if(isset($this->config['multibotConfig'][$commandInfo['command'][1]]))  {
    if($this->getInstanceId($commandInfo['command'][1])) {
      $id = $this->getInstanceId($commandInfo['command'][1]);
      $statuss = $this->sendToInstance($id, "status ".$commandInfo['command'][1]);

      if(!$statuss) {
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
      }else {
        $r = $this->readFromInstance($id);
        $tsAdmin->sendMessage(1, $commandInfo['clientId'], $r);
        if($r == "stop")  {
          $status = $this->sendToInstance($id, "start ".$commandInfo['command'][1]);
          if($status) {
            $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_start_function_start']);
          }else {
            $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_start_function_error']);
          }
        }elseif($r == "run")  {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_start_function_run']);
        }else {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
        }
      }
    }else {
      if(isset($this->conifg['multibotConfig'][$commandInfo['command'][1]]['primary_instance']) && ($this->conifg['multibotConfig'][$commandInfo['command'][1]]['primary_instance'] == true)) {
        $status = $this->sendToInstance(0, "start ".$commandInfo['command'][1]);
        $r = $this->readFromInstance(0);
        if(!$status)  {
          $id = 0;
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
        }elseif($r == "started"){
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_start_function_start']);
        }else {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
        }
      }else {
        $id = getSmallerIndex($this->instanceList['instances']);

        $this->instanceList['instances'][$id]['functions'][] = $commandInfo['command'][1];
        $this->instanceList['instances'][$id]['weight'] = $this->instanceList['instances'][$id]['weight'] + $this->config['multibotConfig'][$commandInfo['command'][1]]['weight'];

        $status = $this->sendToInstance($id, "start ".$commandInfo['command'][1]);
        $r = $this->readFromInstance($id);
        if(!$status)  {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_instance_connect_error'].$id);
        }elseif($r == "started"){
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_start_function_start']);
        }else {
          $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_error']);
        }
      }
    }
  }else {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_unknown_function']);
  }
}
?>
