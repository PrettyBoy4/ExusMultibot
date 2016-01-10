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
  private $tsAdmin;
  protected $start_timer = 0;
  protected $function_name = "Welcome Messge";
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
  	$return = Array();
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
  	$paths = $this->multibotCore->getPaths();
  	$tsAdmin = $this->multibotCore->getTsAdmin();
  	$clientList = Array();
    $currentClientList = $this->convertClientTable($this->multibotCore->getclientList()); // Tworzy i konwertuje do odpowiedniego formatu tablicę z aktualnie obecnymi użytkownikami na serwerze
    $clientList = array_diff($currentClientList, $this->clientListSend); // Porównuje listę użytkowników do ktrych została wysłana wiadomość z użytkowinkami aktualnie obecnymi na serwerze i zwraca tablicę z listą nowych użytkowników
    foreach($clientList as $clid => $clientListTemp)
    { // Pętla wywołuje instrukcje dla każdego użytkownika z tablicy $clientList ([clid] => dbid)
      $message = $this->prepareMessage($paths['folders']['functions-configs'] . "welcomeMessage.txt", $clid); // Tworzy wiadomość dla użytkownika o podanym $clid
      $lol = $tsAdmin->sendMessage(1, $clid, "\n".$message); // Wysyła wiadomość do użytkowinka o podanym $clid
    }
    $this->clientListSend = $currentClientList; // Zapisuje listę aktualnie obecnych użytkowników do listy powiadomionych użytkowników
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
      $this->clientListSend = $this->convertClientTable($multibotCore->getclientList()); // Konwertuje i zapisuje listę użytkowników obecnych na serwerze w momencie tworzenia obiektu
      $this->tsAdmin = $multibotCore->getTsAdmin();
    }
  }
  
  function prepareMessage($file, $clid = null)
  {
  	$serverInfo = $this->multibotCore->getServerInfo();
  	if($clid != null)
  	{
  		$clientInfoTemp = $this->tsAdmin->clientInfo($clid);
  		$clientInfo = $clientInfoTemp['data'];
  		unset($clientInfoTemp);
  		$clientInfo['client_created'] = date('d-m-Y H.i.s', $clientInfo["client_created"]);
  		$clientInfo['client_lastconnected'] = date('d-m-Y H.i.s', $clientInfo["client_lastconnected"]);
  		$serverInfo['virtualserver_uptime'] = $this->tsAdmin->convertSecondsToStrTime($serverInfo['virtualserver_uptime']);
  		$varsMessage = Array(
  				"{cid}",
  				"{client_unique_identifier}",
  				"{client_nickname}",
  				"{client_version}",
  				"{client_platform}",
  				"{client_database_id}",
  				"{client_created}",
  				"{client_lastconnected}",
  				"{client_totalconnections}",
  				"{client_description}" ,
  				"{client_month_bytes_uploaded}",
  				"{client_month_bytes_downloaded}",
  				"{client_total_bytes_uploaded}",
  				"{client_total_bytes_downloaded}",
  				"{client_nickname_phonetic}",
  				"{client_country}",
  				"{client_base64HashClientUID}",
  				"{connection_filetransfer_bandwidth_sent}",
  				"{connection_filetransfer_bandwidth_received}",
  				"{connection_packets_sent_total}",
  				"{connection_bytes_sent_total}",
  				"{connection_packets_received_total}",
  				"{connection_bytes_received_total}",
  				"{connection_bandwidth_sent_last_second_total}",
  				"{connection_bandwidth_sent_last_minute_total}",
  				"{connection_bandwidth_received_last_second_total}",
  				"{connection_bandwidth_received_last_minute_total}",
  				"{connection_connected_time}",
  				"{connection_client_ip}",
  				"{virtualserver_uptime}",
  				"{virtualserver_maxclients}",
  				"{virtualserver_reserved_slots}",
  				"{virtualserver_clientsonline}",
  				"{virtualserver_channelsonline}",
  				"{virtualserver_welcomemessage}",
  				"{virtualserver_name}",
  				"{virtualserver_client_connections}",
  				"{virtualserver_min_client_version}",
  				"{date}"
  				);
  		$valuesMessage = Array(
  				$clientInfo['cid'],
  				$clientInfo['client_unique_identifier'],
  				$clientInfo['client_nickname'],
  				$clientInfo['client_version'],
  				$clientInfo['client_platform'],
  				$clientInfo['client_database_id'],
  				$clientInfo['client_created'],
  				$clientInfo['client_lastconnected'],
  				$clientInfo['client_totalconnections'],
  				$clientInfo['client_description'],
  				$clientInfo['client_month_bytes_uploaded'],
  				$clientInfo['client_month_bytes_downloaded'],
  				$clientInfo['client_total_bytes_uploaded'],
  				$clientInfo['client_total_bytes_downloaded'],
  				$clientInfo['client_nickname_phonetic'],
  				$clientInfo['client_country'],
  				$clientInfo['client_base64HashClientUID'],
  				$clientInfo['connection_filetransfer_bandwidth_sent'],
  				$clientInfo['connection_filetransfer_bandwidth_received'],
  				$clientInfo['connection_packets_sent_total'],
  				$clientInfo['connection_bytes_sent_total'],
  				$clientInfo['connection_packets_received_total'],
  				$clientInfo['connection_bytes_received_total'],
  				$clientInfo['connection_bandwidth_sent_last_second_total'],
  				$clientInfo['connection_bandwidth_sent_last_minute_total'],
  				$clientInfo['connection_bandwidth_received_last_second_total'],
  				$clientInfo['connection_bandwidth_received_last_minute_total'],
  				$clientInfo['connection_connected_time'],
  				$clientInfo['connection_client_ip'],
  				$serverInfo['virtualserver_uptime'],
  				$serverInfo['virtualserver_maxclients'],
  				$serverInfo['virtualserver_reserved_slots'],
  				$serverInfo['virtualserver_clientsonline'],
  				$serverInfo['virtualserver_channelsonline'],
  				$serverInfo['virtualserver_welcomemessage'],
  				$serverInfo['virtualserver_name'],
  				$serverInfo['virtualserver_client_connections'],
  				$serverInfo['virtualserver_min_client_version'],
  				date("m.d.y")
  				);
  		$sourceMessage = file_get_contents($file);
  		$message = str_replace($varsMessage,$valuesMessage,$sourceMessage);
  		return $message;
  	}
  	if($clid == null)
  	{
  		$serverInfo['virtualserver_uptime'] = $this->tsAdmin->convertSecondsToStrTime($this->serverInfo['virtualserver_uptime']);
  		$varsMessage = Array(
  				"{virtualserver_uptime}",
  				"{virtualserver_maxclients}",
  				"{virtualserver_reserved_slots}",
  				"{virtualserver_clientsonline}",
  				"{virtualserver_channelsonline}",
  				"{virtualserver_welcomemessage}",
  				"{virtualserver_name}",
  				"{virtualserver_client_connections}",
  				"{virtualserver_min_client_version}",
  				"{date}"
  				);
  		$valuesMessage = Array(
  				$serverInfo['virtualserver_uptime'],
  				$serverInfo['virtualserver_maxclients'],
  				$serverInfo['virtualserver_reserved_slots'],
  				$serverInfo['virtualserver_clientsonline'],
  				$serverInfo['virtualserver_channelsonline'],
  				$serverInfo['virtualserver_welcomemessage'],
  				$serverInfo['virtualserver_name'],
  				$serverInfo['virtualserver_client_connections'],
  				$serverInfo['virtualserver_min_client_version'],
  				date("m.d.y")
  				);
  		//print_r($varsMessage);
  		$sourceMessage = file_get_contents($file);
  		$message = str_replace($varsMessage,$valuesMessage,$sourceMessage);
  		return $message;
  	}
  }
  
}
?>