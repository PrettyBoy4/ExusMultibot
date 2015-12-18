<?php
/** Referencja do tsAdmin: $this->tsAdmin
  *
  * Kod całego pliku wykonywany jest w przypadku użycia komendy o nazwie pliku.
  *
  * tsAdmin - referencja do obiektu ts3admin
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

// Minimalna ilość argumentów do podania w komendzie
$ilosc_argumentow = 0;


if(count($commandInfo['command']) <= ($ilosc_argumentow + 1)) {

  $instancje = $this->instanceList['instances'];

  $wiadomosc = "\n\n";

  foreach($instancje as $id => $dane) {
    $wiadomosc .= "[b]Id instancji: [/b]" . $id . "\n";

    $wiadomosc .= "  " . "[b]Nazwa procesu: [/b]" . $dane['process'] . "\n";

    $wiadomosc .= "  " . "[b]Nazwa użytkownika query: [/b]" . $dane['bot_name'] . "\n";

    $wiadomosc .= "  " . "[b]Uruchomione funkcje: [/b]\n";

    foreach($dane['functions'] as $f) {
      $wiadomosc .= "    -" . $f . "\n";
    }
  }

  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $wiadomosc);
}else {
  // Wiadomośc wysyłana gdy argumentów jest zbyt mało
  $tsAdmin->sendMessage(1, $commandInfo['clientId'], $this->lang['commands_no_arguments'] . " " . $ilosc_argumentow);
}

?>
