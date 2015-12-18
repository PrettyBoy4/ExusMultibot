<?php
/** welcomeMessage($multibotCore)
  *
  * Wrsja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Wysyła wiadomość powitalną do każdego nowego użytkownika
  *
  * Wymagane zmienne (multibotCore):
  * - clientList
  *
  */
class welcomeMessage extends start
{
  private $multibotCore;
  private $clientListSend = Array();
  protected $timer = 0;
/** convertClientTable($table)
  *
  * $table - Tablica z listą użytkowników tsAdmin
  *
  * Typ: Prywatna
  *
  * Konwertuje tablice z użytkownikami do postaci [clid] => dbid
  *
  */
  private function convertClientTable($table)
  { // Funkcja konwertująca tablice użytkowników do formatu Array([clid] => dbid)
    foreach($table as $tableTemp)
    {
      $return[$tableTemp['clid']] = $tableTemp['client_database_id'];
    }
    return $return;
  }
/** start_function()
  *
  * Typ: Publiczna
  *
  * Główna funkcja welcomeMessage
  *
  * Wymagane zmienne
  * -serverInfo
  *
  */
  public function start_function()
  {
    $currentClientList = $this->convertClientTable($this->multibotCore->clientList); // Tworzy i konwertuje do odpowiedniego formatu tablicę z aktualnie obecnymi użytkownikami na serwerze
    $clientList = array_diff($currentClientList, $this->clientListSend); // Porównuje listę użytkowników do ktrych została wysłana wiadomość z użytkowinkami aktualnie obecnymi na serwerze i zwraca tablicę z listą nowych użytkowników
    foreach($clientList as $clid => $clientListTemp)
    { // Pętla wywołuje instrukcje dla każdego użytkownika z tablicy $clientList ([clid] => dbid)
      $message = $this->multibotCore->prepareMessage("Configs/Functions/welcomeMessage.txt", $clid); // Tworzy wiadomość dla użytkownika o podanym $clid
      $this->multibotCore->tsAdmin->sendMessage(1, $clid, "\n".$message); // Wysyła wiadomość do użytkowinka o podanym $clid
    }
    $this->clientListSend = $currentClientList; // Zapisuje listę aktualnie obecnych użytkowników do listy powiadomionych użytkowników
    return 'WelcomeMessage';
  }
/** __construct($multibotCore)
  *
  * Typ: Konstruktor
  *
  * Przypisuje referencje
  *
  */
  function __construct(multibotCore $multibotCore)
  { // Konstruktor jako argument przyjmuje obiekt multibotCore
    $this->multibotCore = $multibotCore; // Przypisuje referencje multibotCore do $multibotCore
    $multibotCore->refresh('clientList'); // Wymuszenie załadowania licty użytkowników w celu przypisania ich do listy wysłanych po to aby aktualnie obecni użytkownicy przebywający serwerze w czasie uruchamiania bota nie dostali wiadokości powitalnej
    if(empty($this->clientListSend))  {
      $this->clientListSend = $this->convertClientTable($multibotCore->clientList); // Konwertuje i zapisuje listę użytkowników obecnych na serwerze w momencie tworzenia obiektu
    }
  }
}
?>
