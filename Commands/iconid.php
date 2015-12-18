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
  print_r($commandInfo['command']);
  if(!is_int($commandInfo['command'][1] + 0))  {
    $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_iconid_bad_id']);
  }else {
    $channelInfo = $tsAdmin->channelInfo($commandInfo['command'][1]);
    if(!$channelInfo['success']) {
      $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_iconid_bad_channel']);
    }elseif($channelInfo['data']['channel_icon_id'] == 0)  {
      $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_iconid_no_icon']);
    }else {
      $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['command_iconid_icon'].$channelInfo['data']['channel_icon_id']);
    }
  }
}elseif(count($commandInfo['command']) > 2) {
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_too_many_arguments']);
}else {
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_no_arguments']);
}

?>
