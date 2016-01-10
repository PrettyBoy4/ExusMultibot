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

if(count($command_info['command']) > 1)	{
	if(!isset($this->multibot_config[$command_info['command'][1]]))	{
		$tsAdmin->sendMessage(1, $command_info['clientId'], "Podano nazwę nieistniejącej funckji");
	}else{
		
		if(count($command_info['command']) == 3) {
			if(is_int($command_info['command'][2]) && is_string($command_info['command'][1])) {
					$status = $this->startFunction($command_info['command'][1], $command_info['command'][2]);
					if($status == "1") {
						$tsAdmin->sendMessage(1, $command_info['clientId'], "Funkcja została pomyślnie uruchomiona");
					}else {
						$tsAdmin->sendMessage(1, $command_info['clientId'], "Nie udało się uruchomić funkcji");
					}
			}else {
				$tsAdmin->sendMessage(1, $command_info['clientId'], "Muszisz podać argumenty w następującej kolejności \"nazwa_funkcji id_instancji\"");
			}
		}else {
			$status = $this->startFunction($command_info['command'][1]);
			if($status == "1") {
				$tsAdmin->sendMessage(1, $command_info['clientId'], "Funkcja została pomyślnie uruchomiona");
			}else {
				$tsAdmin->sendMessage(1, $command_info['clientId'], "Nie udało się uruchomić funkcji");
			}
		}
	}
}else{
	$tsAdmin->sendMessage(1, $command_info['clientId'], "Funkcja wymaga podania conajmniej jednego argumentu");
}
?>
