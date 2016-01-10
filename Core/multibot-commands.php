<?php
/**
 * @return socket send: badfunction, function return
 */
function commands() {
  global $socket;
  global $multibotObject;
  global $buffer;
  global $functions_to_start;
  global $commands_list;
  if(in_array($buffer[0], $commands_list))  {
    $command = "command_".$buffer[0];
    $return = $command();
    unset($buffer);
    return $return;
  }else {
    $msg = "badfunction";
    socket_write($socket, $msg, strlen($msg));
    return false;
  }
}

/**
 * @return socket send: stoped, stop
 */
function command_stop()  {
  global $buffer;
  global $socket;
  global $functions_to_start;
  if(($buffer[0] == 'stop') && !empty($buffer[1])) {
  	print_r($buffer);
    if(isset($functions_to_start[$buffer[1]])) {
      unset($functions_to_start[$buffer[1]]);
      socketSend("stoped");
      refreshVarsList();
      return true;
    }else {
      socketSend("stop");
      return true;
    }
  }
}

/**
 * 
 * @return socket send: runing, stoped, badfunctionname
 */
function command_status()  {
  global $buffer;
  global $socket;
  global $functions_to_start;
  if(($buffer[0] == "status") && !empty($buffer[1]))  {
    if(isset($functions_to_start[$buffer[1]])) {
      socketSend("runing");
      return true;
    }elseif (!isset($functions_to_start[$buffer[1]])) {
      socketSend("stoped");
      return true;
    }else {
      socketSend("badfunctionname");
      return true;
    }
  }
}

/**
 * @return socket send: started, badfunction, runing
 */
function command_start()  {
  global $buffer;
  global $multibotObject;
  global $socket;
  global $functions_to_start;
  if(($buffer[0] == 'start') && !empty($buffer[1])) {
    if(isset($functions_to_start[$buffer[1]])) {
      socketSend('runing');
    }elseif(!isset($functions_to_start[$buffer[1]])) {
      $functions_to_start[$buffer[1]] = $buffer[1];
      $varName = $buffer[1];
      global $$varName;
      $$varName = new $buffer[1]($multibotObject);
      refreshVarsList();
      socketSend("started");
    }else {
      socketSend("badfunction");
    }
  }
}

/**
 * @return boolean
 */
function command_reloadconfig() {
  global $multibotObject;
  global $buffer;
  global $break;
  global $lang;
  $multibotObject->addInfo($lang['command_reload']['console_info']);
  $status = $multibotObject->setConfig("multibot");
  if($status) {
    socketSend("success");
    //$break = true;
    return true;
  }else {
    socketSend("error");
    return false;
  }
}


?>
