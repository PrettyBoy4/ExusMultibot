<?php
/** Referencja do tsAdmin: $this->tsAdmin
  *
  * Kod całego pliku wykonywany jest w przypadku użycia komendy o nazwie pliku.
  *
  * Tablica command_info
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
if((count($command_info['command']) == 2) && ($command_info['command'][0] == "stop")) {
 if(isset($this->multibot_config[$command_info['command'][1]])) {
 	$status = $this->stopFunction($command_info['command'][1]);
 	if($status) {
 		$tsAdmin->sendMessage(1, $command_info['clientId'], "Pomyślnie wyłączono funkcję");
 	}else {
 		$tsAdmin->sendMessage(1, $command_info['clientId'], "Nie udało się wyłączyć funkji");
 	}
 }else {
 	$tsAdmin->sendMessage(1, $command_info['clientId'], "Muszisz podać poprawną nazwę funkcji");
 }
}else {
	$tsAdmin->sendMessage(1, $command_info['clientId'], "Musisz podać conajmniej jeden argument");
}
?>
