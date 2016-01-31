<?php
class commandsCore extends baseObject {


  //*******************************************************************************************
  //****************************************** Vars *******************************************
  //*******************************************************************************************

  // $lang;
  // $paths;
  // $tsAdmin;

  // $socket;

  // $general_config;
  // $command_list;
  // $multibot_config;
  // $permission_list;

  /**
   * Array
   * (
   *  [instance_id] = id
   *  [instances] => Array
   *   (
   *    [instance_id] => Array
   *     (
   *      [functions] => Array
   *       (
   *        [function_name] => function_name
   *       )
   *      [weight] => functions weight
   *     )
   *    [instance_id2] => Array
   *     (
   *      [functions] => Array
   *       (
   *        [function_name] => function_name
   *       )
   *      [weight] => functions weight
   *     )
   *   )
   * )
   * 
   * @var Array
   */
  private $instance_list = Array();

  //*******************************************************************************************
  //************************************ Public Functions *************************************
  //******************************************************************************************



  /**
   * 
   * @return Array
   */
  public function getInstanceList()  {
    return $this->instance_list;
  }


  /**
   * 
   * @param Array $functions
   * @return boolean|Array
   */
  public function createInstance($functions)  {

    if(empty($functions)) {
      $this->addError("Nie podano funkcji do utworzenia instancji");
      return false;
    }

    if(empty($this->instance_list))  {
      $this->instance_list['id'] = 0;
    }else {
      $this->instance_list['id']++;
    }

    print "\n";
    $this->addInfo("Uruchamianie instancji o id: " . $this->instance_list['id']);

    $result = shell_exec("screen -dmS ExusMultibotInstance php " . $this->paths['files']['core'] . " --startmode multibot --lang pl");

    if($result == "[screen is terminated]") {
      $this->addError("Nie udało się uruchomić instancji o id " . $this->instance_list['id']);
      return false;
    }

    $this->addInfo("Oczekiwanie na połączenie od instacnji o id: " . $this->instance_list['id']);
    socket_listen($this->socket);

    if($this->instance_list['instances'][$this->instance_list['id']]['socket'] = socket_accept($this->socket)) {

      $this->addInfo("Pomyślnie połączono się z instacją o id ". $this->instance_list['id']);

      foreach($functions['functions'] as $function) {
        if(!isset($functions_instance) || empty($functions_instance)) {
          $functions_instance = $function;
        }else {
          $functions_instance .= "," . $function;
        }
      }

      if($this->instance_list['id'] == 0)  {
        $msg = $this->general_config['multibot_config']['instance_name'] . "," . $functions_instance;
      }else {
        $msg = $this->general_config['multibot_config']['instance_name'] . $this->instance_list['id'] . "," . $functions_instance;
      }

      if(!socket_write($this->instance_list['instances'][$this->instance_list['id']]['socket'], $msg, strlen($msg)))
      {
        $this->addError("Komunikacją z instancją (id " . $this->instance_list['id'] . ") nie powiodła się", true);
      }else {
        $this->addInfo("Pomyślnie wyłano instrukcje dla instancji id " . $this->instance_list['id']);
      }
      $this->addInfo("Oczekiwanie na odpowiedź od instancji (3s)");

      sleep(3);

      if($buffer = socket_read($this->instance_list['instances'][$this->instance_list['id']]['socket'], 2048))  {
        $this->addInfo("Informacje o instancji" . "\n");

        print_r(Array(
          'id' => $this->instance_list['id'],
          'protect' => $functions['protect'],
          'Process Name' => 'ExusMultibotInstance',
          'Bot Name' => $buffer,
          'Functions' => $functions['functions'])
        );

        print "\n";

        $this->instance_list['instances'][$this->instance_list['id']] = Array(
          'id' => $this->instance_list['id'],
          'protect' => $functions['protect'],
          'process' => "ExusMultibotInstance",
          'bot_name' => $buffer,
          'functions' => $functions['functions'],
          'weight' => $functions['weight'],
          'socket' => $this->instance_list['instances'][$this->instance_list['id']]['socket']
        );

        return $this->instance_list['id'];
      }
    }else {
      shell_exec("screen -XS ExusMultibotInstance quit");
      $this->addError("Nie udało połączyć się z instancją. Instancja zostanie wyłączona.", true);
    }
  }
  
  
  public function executeCommand($command) {
  
  	if($this->command_list == false) {
  		return 4;
  	}
  	$command_info = Array(
  			'command' => explode(" ", mb_strtolower($command['msg'], "UTF-8")),
  			'clientId' => $command['invokerid'],
  			'clientUID' => $command['invokeruid'],
  			'clientName' => $command['invokername']
  			);
  	
  	foreach($command_info['command'] as $id => $command) {
  		$new_command[$id] = preg_replace('/\s+/', '', $command);
  	}
  	$command_info['command'] = $new_command;
  	
  	$tsAdmin = $this->tsAdmin;
  	if(isset($this->command_list[$command_info['command'][0]]))  {
  
  		$dbid = $this->tsAdmin->clientGetDbIdFromUid($command_info['clientUID']);
  
  		if(!$dbid['success'])  {return false;}
  
  		$client_groups = $this->tsAdmin->serverGroupsByClientID($dbid['data']['cldbid']);
  
  		if(!$client_groups['success']) {return false;}
  
  		if(!isset($this->permission_list["c_permission_".$command_info['command'][0]])) {
  			$this->addError("Nie można odnaleźć permisji" . " c_permission_".$command_info['command'][0]);
  			return 3;
  		}else {
  			$groups = $this->permission_list["c_permission_".$command_info['command'][0]];
  		}
  
  		if(strstr($groups, "all") !== false)  {
  			$function = true;
  			while($function)	{
  				include($this->paths['folders']['commands'] . $this->command_list[$command_info['command'][0]]);
  				break;
  			}
  			return 5;
  		}
  
  		foreach($client_groups['data'] as $group) {
  			if(strstr($groups, $group['sgid'])) {
  				$function = true;
  				while($function) {
  					include($this->paths['folders']['commands'] . $this->command_list[$command_info['command'][0]]);
  					break;
  				}
  				return 5;
  			}
  		}
  		return 2;
  	}else {
  		return 4;
  	}
  }

  //*******************************************************************************************
  //*********************************** Internal Functions ************************************
  //*******************************************************************************************

  
  /** killAllInstances()
   *
   * Wyłącza wszystkie instancje
   *
   * @return true
   */
  protected function killAllInstances() {
  	foreach($this->instance_list['instances'] as $instance)  {
  		$this->killInstance($instance['id']);
  	}
  	return true;
  }
  
  
  /**
   *
   * @param string $function_name
   * @param int $instance_id
   * @return 2 - function was runing, 1 - function have been started, false - error, 3 - bad function
   */
  private function startFunction($function_name, $instance_id = false)	{
  	$function_name = mb_strtolower($function_name, "UTF-8");
  	if(!isset($this->multibot_config[$function_name]))	{
  		return "3";
  	}
  	if(($finstance_id = $this->getInstanceId($function_name)) && !$instance_id)	{
  		$this->sendToInstance($finstance_id, "start " . $function_name);
  		$status = $this->readFromInstance($finstance_id);
  		if($status == "runing")	{
  			return "2";
  		}elseif($status == "started")	{
  			return "1";
  		}else{
  			return false;
  		}
  	}elseif($instance_id) {
  		if($finstance_id = $this->getInstanceId($function_name))	{
  			$this->unsetFunction($function_name);
  		}
  		if(!isset($this->instance_list['instances'][$instance_id]))	{
  			return false;
  		}else {
  			$this->sendToInstance($instance_id, "start " . $function_name);
  			$status = $this->readFromInstance($instance_id);
  			if($status == "runing")	{
  				return "2";
  			}elseif($status == "started")	{
  				return "1";
  			}else{
  				return false;
  			}
  		}
  	}else {
  		if($this->general_config['multibot_config']['protect_primary_instance'])	{
  			if($this->multibot_config[$function_name]['general_config']['primary_instance']) {
  				$this->addFunction($function_name, 0);
  				$this->refreshFunctionsStatus();
  				return "1";
  			}else {
  				$instance_list = $this->instance_list['instances'];
  				unset($instance_list[0]);
  				$instance_id = getSmallerIndex($instance_list);
  				$this->addFunction($function_name, $instance_id);
  				$this->refreshFunctionsStatus();
  				return "1";
  			}
  		}
  		$instance_id = getSmallerIndex($this->instance_list['instances']);
  		$this->addFunction($function_name, $instance_id);
  		$this->refreshFunctionsStatus();
  		return "1";
  	}
  }
  
  
  private function stopFunction($function_name) {
  	mb_strtolower($function_name, "UTF-8");
  	if(isset($this->multibot_config[$function_name])) {
  		$instance_id = $this->getInstanceId($function_name);
  		$this->sendToInstance($instance_id, "stop ". $function_name);
  		while($this->readFromInstance($instance_id)) {
  			$this->sendToInstance($instance_id, "status ". $function_name);
  			$status = $this->readFromInstance($instance_id);
  			break;
  		}
  		if($status == "stoped") {
  			$this->unsetFunction($function_name, $instance_id);
  			print "1";
  			return true;
  		}else {
  			print $status;
  			return false;
  		}
  	}else {
  		print "3";
  		return false;
  	}
  }
  
  private function functionStatus($function_name) {
  	$function_name = mb_strtolower($function_name, "UTF-8");
  	if(!isset($this->multibot_config[$function_name])) {
  		return false;
  	}else
  		if($instance_id = $this->getInstanceId($function_name)) {
  			$this->sendToInstance($instance_id, "status " . $function_name);
  			$status = $this->readFromInstance($instance_id);
  			if($status == "runing") {
  				return true;
  			}else {
  				return false;
  			}
  		}else {
  			return false;
  		}
  }
  
  private function refreshFunctionsStatus() {
  	$functions = Array();
  	foreach($this->instance_list['instances'] as $instance_id => $instance_info) {
  		foreach($instance_info['functions'] as $function_name) {
  			if(in_array($function_name, $functions)) {
  				$this->unsetFunction($function_name, $instance_id);
  				$this->sendToInstance($instance_id, "stop ". $function_name);
  			}else {
  				if($this->functionStatus($function_name)) {
  					$functions[$function_name] = $function_name;
  				}else {
  					$this->sendToInstance($instance_id, "start ". $function_name);
  					$functions[$function_name] = $function_name;
  				}
  			}
  		}
  	}
  }
  
  /**
   *
   * @param string$function_name
   * @return boolean
   */
  private function unsetFunction($function_name, $sinstance_id = false)	{
  	if($sinstance_id && isset($this->instance_list['instances'][$sinstance_id]['functions'][$function_name])) {
  		unset($this->instance_list['instances'][$sinstance_id]['functions'][$function_name]);
  		$this->instance_list['instances'][$sinstance_id]['weight'] -= $this->multibot_config[$function_name]['general_config']['weight'];
  		return true;
  	}else {
  		foreach($this->instance_list['instances'] as $instance_id => $instance)	{
  			if(isset($instance['functions'][$function_name]))	{
  				unset($this->instance_list['instances'][$instance_id]['functions'][$function_name]);
  				$this->instance_list['instances'][$instance_id]['weight'] -= $this->multibot_config[$function_name]['general_config']['weight'];
  				$status = true;
  			}
  		}
  		if($status){
  			return true;
  		}else{
  			return false;
  		}
  	}
  }
  
  private function addFunction($function_name, $instance_id) {
  	$this->unsetFunction($function_name);
  	$this->instance_list['instances'][$instance_id]['functions'][$function_name] = $function_name;
  	$this->instance_list['instances'][$instance_id]['weight'] += $this->multibot_config[$function_name]['general_config']['weight'];
  	return true;
  }
  
  /** getInstanceId($function)
   *
   * Zwraca id instancji w której uruchomiona jest podana funkcja
   *
   * @param string $function
   * @return int|boolean
   */
  private function getInstanceId($function)  {
  	foreach($this->instance_list['instances'] as $instance_id => $instance_info) {
  		if(in_array($function, $instance_info['functions']))  {
  			return $instance_id;
  		}
  	}
  	return false;
  }
  
  
  
  
  
  /** sendToInstance($id, $msg)
   *
   * Wysyła polecenie do instancji o id $id
   *
   * @param int @id
   * @param string @msg
   * @return boolean
   */
  private function sendToInstance($id, $msg)  {
  	if(is_int($id)) {
  		$socket = $this->instance_list['instances'][$id]['socket'];
  	}else {
  		if($instance_id = $this->getInstanceId($id)) {
  			$socket = $this->instance_list['instances'][$instance_id]['socket'];
  		}else {
  			return false;
  		}
  	}
  
  	if(!socket_write($socket, $msg, strlen($msg))) {
  		return false;
  	}else {
  		return true;
  	}
  }
  
  
  
  
  /** readFromInstance($id)
   *
   * Odczytuje informacje otrzymane od instancji
   *
   * @param int $id
   * @return boolean|sting
   */
  private function readFromInstance($id)  {
  	if(is_int($id)) {
  		$socket = $this->instance_list['instances'][$id]['socket'];
  	}else {
  		if($instance_id = $this->getInstanceId($id)) {
  			$socket = $this->instance_list['instances'][$instance_id]['socket'];
  		}else {
  			return false;
  		}
  	}
  
  	sleep(1);
  
  	if(!($buffer = socket_read($socket, 2048))) {
  		return false;
  	}else {
  		return $buffer;
  	}
  }
  
  
  
  
  /** killInstance($id)
   *
   * Wyłącza daną instancje
   *
   * @param int $id
   * @return boolean
   */
  private function killInstance($id)  {
  	if(!is_int($id))  {
  		$id = $this->getInstanceId($id);
  	}
  
  	if(isset($this->instance_list['instances'][$id]))  {
  		$return = shell_exec("screen -XS ". $this->instance_list['instances'][$id]['process'] ." quit");
  	}else {
  		return false;
  	}
  
  	if($return == "No screen session found.") {
  		$this->addError("Nie można odlaźć screen'a ". $this->config['instances'][$id]['process']);
  		return false;
  	}elseif ($return = "") {
  		return true;
  	}else {
  		return false;
  	}
  }
  
  
}
?>
