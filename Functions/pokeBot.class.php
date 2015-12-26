<?php
/** pokebot($multibotCore)
  *
  * Wersja: Alpha 1.1.3
  * Data wydania: 25.11.2015
  *
  * Wysyła poke do administracji kiedy użytkownik wejdzie na dany kanał
  *
  * Wymagane zmienne (multibotCore):
  * - clientList (Zalecany czas odświeżania nie większy niż czas oświerznia pokebota)
  * - channelList (Zalecany czas odświeżania nie większy niż czas oświerznia pokebota)
  *
  */
class pokebot extends start
{
  private $multibotCore;
  public $poketimer;
  private $timeOfflineStatus = 0;
  private $zmienna;
  private $sendedMessages = Array();
  private $send = Array();
  protected $timer = 0;

	#Funkcja zwracajaca tablice z informacjami o uzytkownikach obecnych na kanale $config['channel_id']
	private function usersOnChannel()
  {

		#Pobieranie listy uzytkownikow serwera
		$clientList = $this->multibotCore->clientList;

		#Wyszukiwanie uzytkowikow z danego kanalu
		foreach($clientList as $clientListTemp)
    {
			#Sprawdzanie na jakim kanale znajduje sie użytkownik jeżeli na właściwym przechodzi dalej
			if(($clientListTemp['cid'] == $this->multibotCore->config['multibotConfig']['pokeBot']['channel_id']) && ($clientListTemp['client_type'] == 0))
      {
				#Przypisywanie danych uzytkownika do tymczasowej tablicy
				#{
					$table['clientNickname'] = $clientListTemp['client_nickname'];

					$table['clientDatabaseId'] = $clientListTemp['client_database_id'];

					$table['clientCurrentId'] = $clientListTemp['clid'];
				#}

				#Przypisywanie danych z tymczasowej tablicy do tablicy wyjściowej
				$usersTable[] = $table;
			}
		}
		#Zwraca tablice z użytkownikami obecnymi na kanale
		return $usersTable;
	}

/** channelStatus()
  *
  * Typ: Prywatna
  *
  * Sprawdza czy kanał jest pusty jeśli nie zwraca true
  *
  */
	private function channelStatus()
  {

		#Pobieranie listy kanalow
		$channelList = $this->multibotCore->channelList;

		#Wyszukiwanie kanalu
		foreach($channelList as $channelListTemp)
    {
			#Jesli znaleziono kanal o podanym id
			if($channelListTemp['cid'] == $this->multibotCore->config['multibotConfig']['pokeBot']['channel_id'])
      {
				#Jesli na kanale są użytkownicy
				if($channelListTemp['total_clients'] > 0)
        {
					#Zwraca true
					return true;
				}

				#Jeśli status miernika jest = 0 to ustawia poketmier na czas podany w $config['time_offile']
				if($this->timeOfflineStatus > 0)
        {
					#Ustawia poketimer na czas podany w $config['time_offline']
					$this->poketimer = date('r', time() + $this->multibotCore->config['multibotConfig']['pokeBot']['time_offline']);

					#Zmienia status miernika na  = 1 dzięki czemu przy następnym sprawdzaniu kanału wartość poketimer nie zostanie zwiększona
					$this->timeOfflineStatus = 0;
				}

				#Zwraca false jezeli nie ma uzytkownikow na kanale
				return false;
			}
		}
	}

/** poke()
  *
  * Typ: Prywatna
  *
  * Wysyła poke do administratora jeśli wysłano zwraca true
  *
  */
	private function poke()
  {
    $tsAdmin = $this->multibotCore->tsAdmin;

		#Tablica zawierajaca id grup administratorow tworzona z $config['group']
		$adminGroups = explode(',', $this->multibotCore->config['multibotConfig']['pokeBot']['group']);

		#Tablica zawierajaca liste administratorów
		$adminList = Array();

		#Sprawdzanie czy aktualna data jest większa niż ta zapisana w $poketimer
		if(date('r') > $this->poketimer)
    {
			#Wybieranie każdej grupy z listy grup administracyjnych
			foreach($adminGroups as $adminGroupsTemp)
      {
				#Pobieranie listy użytkowników wybranej grupy
				$serverGroupClientList = $tsAdmin->serverGroupClientList($adminGroupsTemp, true);


				#Pomijanie iteracji jeżeli administrator jest offline
				if(empty($serverGroupClientList['data'][0]['client_unique_identifier']))
        {
					continue;
				}

				#Wybiera kolejno każdego użytkownika grupy
				foreach($serverGroupClientList['data'] as $serverGroupClientListTemp)
        {
					#Sprawdzanie czy użytkownik jest aktualnie na serwerze oraz pobiera jego id
					$adminInfo = $tsAdmin->clientGetIds($serverGroupClientListTemp['client_unique_identifier']);

					#Jeżeli znaleziono użytkownika
					if($adminInfo['success'])
          {
						#Przypisuje jego dane do tablicy $adminList
						#{
							$table['clid'] = $adminInfo['data'][0]['clid'];
							$table['clientNickname'] = $serverGroupClientListTemp['client_nickname'];
							$table['uid'] = $adminInfo['data'][0]['cluid'];

							$adminList[] = $table;
						#}
					}
				}
			}
		}

		#Jeżeli jestnieje tablica $adminList czyli conajmniej jeden admin jest online
		if($adminList)
    {
			#Wybieranie każdego użytkownika z listy administratorów $adminList
			foreach($adminList as $adminListTemp)
      {
				#Wysyła poke do wybranego użytkownika
				$tsAdmin->clientPoke($adminListTemp['clid'], $this->multibotCore->config['multibotConfig']['pokeBot']['poke_message']);

				#Ustawia status miernika czasu na 1 dzięki czmu przy sprawdzaniu statusu kanału $poketimer zostanie zwiększony
				$this->timeOfflineStatus = 1;
			}

			#Zwiększanie licznika $poketimer o czas podany $config['time']
			$this->poketimer = date('r', time() + $this->multibotCore->config['multibotConfig']['pokeBot']['time']);

			#Zwraca wartość true
			return true;
		}
		#Jeżeli $poketimer jest większy od aktualnej daty (poke został wcześniej wysłany)
		elseif($this->poketimer > date('r'))  {
			#Zwraca wartosc true
			return true;
		}
		#Jezeli lista administratorów nie istnieje
		else {
			#Zwraca wartosc false
			return false;
		}
	}

/** start_function()
  *
  * Typ: Publiczna
  *
  * Uruchamia pokebota
  *
  */

  protected function start_function()
  {

    $tsAdmin = $this->multibotCore->tsAdmin;

    $channelStatus = $this->channelStatus();

    //print 'asd';
    //print_r($channelStatus);

    #Jeżeli wartość zwrotna funkcji channelStatus() jest równa true czyli kanał nie jest pusty
    if($channelStatus == true)
    {
      #Pobiera dane użytkowników na kanale za pomocą funkcji usersOnChannel()
      $usersOnChannel = $this->usersOnChannel();

      if(!empty($usersOnChannel))
      #Tworzenie listy użytkowników obecnych na kanale zawierające ich id [id_użytkowinka] = [id_użytkownika]
      foreach($usersOnChannel as $usersOnChannelTemp) {
        $this->send[$usersOnChannelTemp['clientCurrentId']] = $usersOnChannelTemp['clientCurrentId'];
      }

      #Porównywanie listy użytkowników obecnych na kanale i użytkowników do któych wiadomość została wysłana w rezultacie otrzymuję listę z użytkownikami którzy nie dostali jeszcze wiadomości oraz których nie było na kanale w poprzedniej iteracji pentli
      $diff = array_diff($this->send, $this->sendedMessages);

      #Wysyłanie poke do wszystkich administratorów oraz zapis wartości zwrotnej funkcji poke() w zmiennej $pokeStatus
      $pokeStatus = $this->poke();

      #Jeżeli wartość zwrotna funkcji poke() = true
      if($pokeStatus == true)
      {
        #Wybiera każdego użytkownika z listy użytkowników którzy nie dostali wiadomości
        foreach($diff as $diffTemp)
        {
          #Wysyłanie wiadomości do użytkowników z listy z inforacją o powiadomieniu administratora
          $tsAdmin->sendMessage(1, $diffTemp, $this->multibotCore->config['multibotConfig']['pokeBot']['Admin_online']);
        }
      }
      #Jeżeli wartość funkcji poke() = false
      else {
        #Wybiera każdego użytkownika z listy użytkowników którzy nie dostali wiadomości
        foreach($diff as $diffTemp)
        {
          #Wysyłanie wiadomości do użytkowników z listy z inforacją o braku powiadomieniu administratora
          $tsAdmin->sendMessage(1, $diffTemp, $this->multibotCore->config['multibotConfig']['pokeBot']['Admin_offline']);
        }
      }
#sadasd
      #Czyszczenie tablicy z listą użytkowników do któych została wysłana wiadomośc w poprzedniej iteracji
      $this->sendedMessages = Array();

      #Tworzenie listy użytkowników do których została wysłana wiadomośc a aktualnej iteracji
      foreach($this->send as $sendTemp)
      {
        $this->sendedMessages[$sendTemp] = $sendTemp;
      }

      #Czyszczenie tablicy z listą użytkowników aktualnie obecnych na kanale
      $this->send = Array();
    }
    #Jeżeli nie ma żadnych użytkowników na kanale
    else
    {
      #Czyszczenie tablicy z listą użytkowników do których została wysłana wiadomośc w poprzedniej iteracji
      $this->sendedMessages = Array();
    }
    return 'PokeBot';
  }

  /** __construct($multibotCore)
    *
    * Typ: Konstruktor
    *
    * Przypisuje referencje
    *
    */
    function __construct(multibotCore $multibotCore)
    {
      $this->multibotCore = $multibotCore;
      $this->poketimer = date('r');
    }
}
?>
