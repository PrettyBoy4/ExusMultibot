<?php
/** afkAutoMove(multibotCore)
  *
  * Wersja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Przenosi osoby afk na wyznaczony kanał.
  * Do konfiguracji w pliku konfiguracyjnym.
  *
  * Wymagane zmienne (multibotCore):
  * - clientList
  *
  */
class afkAutoMove extends start
{
  private $multibotCore;
  private $clientListAFK = Array();
  private $clientAFKmoved = Array();
  private $ignoredChannels = Array();
  private $ignoredUsers = Array();
  private $ignoredGroups = Array();
  protected $timer = 0;
/** start_function()
  *
  * Typ: Chroniona
  *
  */
  protected function start_function()
  { // Uruchamia autoAFK
    $this->refreshIgnored(); // Odswierza warunki nieprzeniesienia
    foreach($this->multibotCore->clientList as $clientListTemp)
    { // Wyniera każdego użytkownika z serwera
      if(($clientListTemp['client_away'] == 1) || ($clientListTemp['client_input_muted'] == 1) && ($clientListTemp['cid'] != $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_channel']))
      { // Sprawdza czy użytkownik jest wyciszony o czy nie jest już przeniesiony
        $time = date('r', time() + $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_time_to_move']); // Ustawia czas po którym użytkownik zostanie przeniesiony
        $this->clientListAFK[$clientListTemp['clid']] = Array('cid' => $clientListTemp['cid'], 'time' => $time); // Dodaje wyciszonego użytkownika do tablicy $clienkListAFK
      }
        $userGroups = explode(',', $clientListTemp['client_servergroups']); // Rozbija do tablicy grupy wybranego użytkownika
        $ignore = false; // Tworzy zmienną $ignore
        foreach($userGroups as $userGroupsTemp)
        { // Wybera każdą grupe użytkownika
          foreach($this->ignoredGroups as $ignoredGroupsTemp)
          { // Wybiera każdą ignorowaną grupe
            if($userGroupsTemp == $ignoredGroupsTemp){$ignore = true;} // Sprawdza czy użytkownik posiada ignorowane grupy jeśli tak ustawia zmienną $ignore na false dzięki czemu następne warunek nie odbywa się
          }
      }
    //  $userChannel = $this->multibotCore->tsAdmin->channelInfo($clientListTemp['cid']); // Pobiera informacje o kanale na jakim znajduje się użytkownik
    //  $userChannelFlag = explode('-', $userChannel['data']['channel_name_phonetic']); // Rozbija channel_name_phonetic na tablice zawierającą znaczniki
      if(in_array($clientListTemp['client_database_id'], $this->ignoredUsers) || in_array($clientListTemp['cid'], $this->ignoredChannels) || $ignore) // Sprawdza czy użytkownik spełnia warunki żeby zignorować jego wyciszenie
      {
       unset($this->clientListAFK[$clientListTemp['clid']]); // Jeśli spełnia usuwa go z tablicy $clientListAFK
      }
    }
    if(!empty($this->clientListAFK))
    { // Sprawdza czy są jacyś wyciszenie użytkownicy
      $time = date('r'); // Zapisuje aktualny czas
      foreach($this->clientListAFK as $value => $clientListAFKtemp)
      { // Wybiera każdego wyciszonego użytkownika
        $clientInfo = $this->multibotCore->tsAdmin->clientInfo($value); // Pobiera informacje o wybranym użytkowniku
        if(($clientInfo['data']['client_away'] == 0) && ($clientInfo['data']['client_input_muted'] == 0))
        { // Sprawdza czy uzytkownik się odciszył
          unset($this->clientListAFK[$value]); // Jeśli tak usuwa go z listy $clientListAFK
        }
        else
        {
          if(($clientListAFKtemp['time'] <= $time) && $this->multibotCore->tsAdmin->clientMove($value, $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_channel']))
          { // Sprawdza czy czas od wyciszenie użytkownika pozwala na jego przeniesienie jeśli tak przenosi
            $this->clientAFKmoved[$value] = $clientListAFKtemp; // Przpisuje użytkownika z $clientListAFK do $clientAFKmoced
            unset($this->clientListAFK[$value]); // Usuwa użytkownika z $clientListAFK
          }
        }
      }
    }
    if(!empty($this->clientAFKmoved))
    { // Sprawdza czy jakiś użytkownik został przeniesiony
      foreach($this->clientAFKmoved as $value => $clientAFKmovedTemp)
      { // Wybiera każdego przeniesionego użytkownika
        $clientInfo = $this->multibotCore->tsAdmin->clientInfo($value); // Pobiera informacje o wybranym użytkowniku
        if(($clientInfo['data']['client_away'] == 0) && ($clientInfo['data']['client_input_muted'] == 0))
        { // Sprawdza czy użytkownik się odciszył
          if($this->multibotCore->tsAdmin->clientMove($value, $clientAFKmovedTemp['cid']))
          { // Przenosi użytkownika spowrotem na jego kanał i usuwa go z tablicy $clientAFKmoved jeżeli przenoszenie się powiodło
            unset($this->clientAFKmoved[$value]); // Usuwa z tablicy $clientAFKmoved
          }
        }
      }
    }
    return 'AfkAutoMove';
  }
/** __construct(multibotCore)
  *
  * Typ: Konstruktor
  *
  * Przypisuje referencje
  *
  */
  function __construct(multibotCore $multibotCore)
  { // Przypisuje referencje multibotCore
    $this->multibotCore = $multibotCore;
  }
  public function refreshIgnored() { // odświeża warunki ignorowana przeniesienia
    $this->ignoredChannels = explode(',', $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_channels']);
    $this->ignoredChannels[] = $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_channel'];
    $this->ignoredGroups = explode(',', $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_groups']);
    $this->ignoredUsers = explode(',', $this->multibotCore->config['multibotConfig']['afkAutoMove']['afk_users']);
  }
}
?>
