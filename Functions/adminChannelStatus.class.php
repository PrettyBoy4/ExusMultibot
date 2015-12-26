<?php
class adminChannelStatus extends start  {
//*******************************************************************************************
//****************************************** Vars *******************************************
//*******************************************************************************************

private $multibotCore;
private $adminsOnline = Array();
private $adminsBusy = Array();
protected $timer = 0;

//*******************************************************************************************
//************************************ Public Functions *************************************
//******************************************************************************************

public function start_function() {
  $config = $this->multibotCore->config['multibotConfig']['adminChannelStatus'];
  $tsAdmin = $this->multibotCore->tsAdmin;

  foreach($config as $chid => $uid)  {
    if(is_int($chid))  {
      $status = $tsAdmin->clientGetids($uid);
      foreach($this->multibotCore->clientList as $values) {
        if($values['client_unique_identifier'] == $uid) {
          foreach(explode(",", $values['client_servergroups']) as $groups)  {
            if($groups == $config['group_busy'])  {
              $busy = true;
              break;
            }else {
              $busy = false;
            }
          }
          break;
        }else {
          $busy = false;
        }
      }
      $channelInfo = $tsAdmin->channelInfo($chid);
      if($busy && !in_array($uid, $this->adminsBusy))  {
        $this->setChannel($chid, $channelInfo['data'], true, true);
        $this->adminsBusy[$uid] = $uid;
        $this->adminsOnline[$uid] = $uid;
      }elseif(!$busy && in_array($uid, $this->adminsBusy)) {
        unset($this->adminsBusy[$uid]);
        unset($this->adminsOnline[$uid]);
      }elseif(!empty($status['data'][0]['clid']) && !in_array($uid, $this->adminsOnline)) {
        $this->setChannel($chid, $channelInfo['data'], true);
        $this->adminsOnline[$uid] = $uid;
      }elseif(empty($status['data'][0]['clid']) && in_array($uid, $this->adminsOnline)) {
        $this->setChannel($chid, $channelInfo['data'], false);
        unset($this->adminsOnline[$uid]);
        if(isset($this->adminsBusy[$uid]))  {
          unset($this->adminsBusy[$uid]);
        }
      }elseif(empty($status['data'][0]['clid']) && !in_array($uid, $this->adminsOnline)) {
        $this->setChannel($chid, $channelInfo['data'], false);
      }
    }
  }
  return 'adminChannelStatus';
}

private function setChannel($chid, $channelInfo, $status, $busy = false) {
  $tsAdmin = $this->multibotCore->tsAdmin;
  $config = $this->multibotCore->config['multibotConfig']['adminChannelStatus'];

  if(!isset($config[$chid . "_type"]))  {
    $type = preg_replace('/\s+/', '', $config['type']);
    $type = explode(",", $type);

    if(in_array("prefix", $type)) {
      $prefix = true;
    }else {
      $prefix = false;
    }

    if(in_array("postfix", $type) && ($prefix == false))  {
      $postfix = true;
    }else {
      $postfix = false;
    }

    if(in_array("icon", $type)) {
      $icon = true;
    }else {
      $icon = false;
    }
  }else {
    $type = preg_replace('/\s+/', '', $config[$chid . "_type"]);
    $type = explode(",", $type);

    if(in_array("prefix", $type)) {
      $prefix = true;
    }else {
      $prefix = false;
    }

    if(in_array("postfix", $type) && ($prefix == false))  {
      $postfix = true;
    }else {
      $postfix = false;
    }

    if(in_array("icon", $type)) {
      $icon = true;
    }else {
      $icon = false;
    }
  }

  if(isset($config[$chid . "_prefix_online"])) {
    $prefix_online = $chid . "_prefix_online";
  }else {
    $prefix_online = "prefix_online";
  }

  if(isset($config[$chid . "_prefix_offline"]))  {
    $prefix_offline = $chid . "_prefix_offline";
  }else {
    $prefix_offline = "prefix_offline";
  }

  if(isset($config[$chid . "_prefix_busy"]))  {
    $prefix_busy = $chid . "_prefix_busy";
  }else {
    $prefix_busy = "prefix_busy";
  }

  if(isset($config[$chid . "_postfix_online"]))  {
    $postfix_online = $chid . "_postfix_online";
  }else {
    $postfix_online = "postfix_online";
  }

  if(isset($config[$chid . "_postfix_offline"])) {
    $postfix_offline = $chid . "_postfix_offline";
  }else {
    $postfix_offline = "postfix_offline";
  }

  if(isset($config[$chid . "_postfix_busy"])) {
    $postfix_busy = $chid . "_postfix_busy";
  }else {
    $postfix_busy = "postfix_busy";
  }

  if(isset($config[$chid . "_join_power_offline"]))  {
    $join_power_offline = $chid . "_join_power_offline";
  }else {
    $join_power_offline = "join_power_offline";
  }

  if(isset($config[$chid . "_join_power_online"])) {
    $join_power_online = $chid . "_join_power_online";
  }else {
    $join_power_online = "join_power_online";
  }

  if(isset($config[$chid . "_join_power_busy"]))  {
    $join_power_busy = $chid . "_join_power_busy";
  }else {
    $join_power_busy = "join_power_busy";
  }

  if(isset($config[$chid . "_icon_online"])) {
    $icon_online = $chid . "_icon_online";
  }else {
    $icon_online = "icon_online";
  }

  if(isset($config[$chid . "_icon_offline"])) {
    $icon_offline = $chid . "_icon_offline";
  }else {
    $icon_offline = "icon_offline";
  }

  if(isset($config[$chid . "_icon_busy"])) {
    $icon_busy = $chid . "_icon_busy";
  }else {
    $icon_busy = "icon_busy";
  }

  if(isset($config[$chid . "_max_clients_online"])) {
    $max_clients_online = $chid . "_max_clients_online";
  }else {
    $max_clients_online = "max_clients_online";
  }

  if(isset($config[$chid . "_max_clients_offline"])) {
    $max_clients_offline = $chid . "_max_clients_offline";
  }else {
    $max_clients_offline = "max_clients_offline";
  }

  if(isset($config[$chid . "_max_clients_busy"])) {
    $max_clients_busy = $chid . "_max_clients_busy";
  }else {
    $max_clients_busy = "max_clients_busy";
  }

  $permList = $tsAdmin->channelPermList($chid, true);

  foreach($permList['data'] as $temp) {
    $perms[$temp['permsid']] = $temp['permvalue'];
  }

  if($status && ($busy == false)) {
    // Ustawienie join power
    if(!empty($config[$join_power_online]) || ($config[$join_power_online] == "0"))  {
      if(!isset($perms['i_channel_needed_join_power'])) {
        $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_online]));
      }else {
        if($perms['i_channel_needed_join_power'] != $config[$join_power_offline]) {
          $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_online]));
        }
      }
    }

    // Ustawienie max clients
    if(!empty($config[$max_clients_online]) || ($config[$max_clients_online] == "0")) {
      if($channelInfo['channel_maxclients'] != $config[$max_clients_online]) {
        $tsAdmin->channelEdit($chid, Array('channel_maxclients' => $config[$max_clients_online]));
      }
    }

    if(!empty($config[$icon_online])) {
      if($icon && ($channelInfo['channel_icon_id'] != $config[$icon_online])) {
        $tsAdmin->channelEdit($chid, Array('channel_icon_id' => $config[$icon_online]));
      }
    }



    if(($prefix || $postfix) && ((strstr($channelInfo['channel_name'], $config[$prefix_offline]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_offline]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_busy]) !== false) || (strstr($channelInfo['channel_name'], $config[$prefix_busy]) !== false)))  {
      $vars = Array(
        $config[$prefix_online] . " ",
        $config[$prefix_offline] . " ",
        " " . $config[$postfix_online],
        " " . $config[$postfix_offline],
        $config[$prefix_busy] . " ",
        " " . $config[$postfix_busy],
      );

      $channelInfo['channel_name'] = str_replace($vars, "", $channelInfo['channel_name']);

      if($prefix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_online] . " " . $channelInfo['channel_name']));
      }elseif($postfix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_online]));
      }elseif($prefix && !$busy)  {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_online] . " " . $channelInfo['channel_name']));
      }elseif($postfix && !$busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_online]));
      }
    }elseif($prefix && !(strstr($channelInfo['channel_name'], $config[$prefix_online]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_online]. " " . $channelInfo['channel_name']));
    }elseif($postfix && !(strstr($channelInfo['channel_name'], $config[$postfix_online]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name']. " " .$config[$postfix_online]));
    }


  }elseif(($status == false) && ($busy == false)) { // Dla offline

    if(!empty($config[$join_power_offline]) || ($config[$join_power_offline] == "0"))  {
      if(!isset($perms['i_channel_needed_join_power'])) {
        $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_offline]));
      }else {
        if($perms['i_channel_needed_join_power'] != $config[$join_power_offline]) {
          $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_offline]));
        }
      }
    }

    // Ustawienie max clients
    if(!empty($config[$max_clients_offline]) || ($config[$max_clients_offline] == "0"))  {
      if($channelInfo['channel_maxclients'] != $config[$max_clients_offline]) {
        $tsAdmin->channelEdit($chid, Array('channel_maxclients' => $config[$max_clients_offline]));
      }
    }

    if(!empty($config[$icon_offline]))  {
      if($icon && ($channelInfo['channel_icon_id'] != $config[$icon_offline])) {
        $tsAdmin->channelEdit($chid, Array('channel_icon_id' => $config[$icon_offline]));
      }
    }


    if(($prefix || $postfix) && ((strstr($channelInfo['channel_name'], $config[$prefix_online]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_online]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_busy]) !== false) || (strstr($channelInfo['channel_name'], $config[$prefix_busy]) !== false)))  {
      $vars = Array(
        $config[$prefix_online] . " ",
        $config[$prefix_offline] . " ",
        " " . $config[$postfix_online],
        " " . $config[$postfix_offline],
        $config[$prefix_busy] . " ",
        " " . $config[$postfix_busy],
      );

      $channelInfo['channel_name'] = str_replace($vars, "", $channelInfo['channel_name']);

      if($prefix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_offline] . " " . $channelInfo['channel_name']));
      }elseif($postfix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_offline]));
      }elseif($prefix && !$busy)  {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_offline] . " " . $channelInfo['channel_name']));
      }elseif($postfix && !$busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_offline]));
      }
    }elseif($prefix && !(strstr($channelInfo['channel_name'], $config[$prefix_offline]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_offline]. " " . $channelInfo['channel_name']));
    }elseif($postfix && !(strstr($channelInfo['channel_name'], $config[$postfix_offline]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name']. " " .$config[$postfix_offline]));
    }


  }elseif($busy){
    if(!empty($config[$join_power_busy]) || ($config[$join_power_busy] == "0"))  {
      if(!isset($perms['i_channel_needed_join_power'])) {
        $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_busy]));
      }else {
        if($perms['i_channel_needed_join_power'] != $config[$join_power_busy]) {
          $tsAdmin->channelAddPerm($chid, Array('i_channel_needed_join_power' => $config[$join_power_busy]));
        }
      }
    }

    // Ustawienie max clients
    if(!empty($config[$max_clients_busy]) || ($config[$max_clients_busy] == "0"))  {
      if($channelInfo['channel_maxclients'] != $config[$max_clients_busy]) {
        $tsAdmin->channelEdit($chid, Array('channel_maxclients' => $config[$max_clients_busy]));
      }
    }

    if(!empty($config[$icon_busy]))  {
      if($icon && ($channelInfo['channel_icon_id'] != $config[$icon_busy])) {
        $tsAdmin->channelEdit($chid, Array('channel_icon_id' => $config[$icon_busy]));
      }
    }

    if(($prefix || $postfix) && ((strstr($channelInfo['channel_name'], $config[$prefix_online]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_online]) !== false) || (strstr($channelInfo['channel_name'], $config[$postfix_offline]) !== false) || (strstr($channelInfo['channel_name'], $config[$prefix_offline]) !== false)))  {
      $vars = Array(
        $config[$prefix_online] . " ",
        $config[$prefix_offline] . " ",
        " " . $config[$postfix_online],
        " " . $config[$postfix_offline],
        $config[$prefix_busy] . " ",
        " " . $config[$postfix_busy],
      );

      $channelInfo['channel_name'] = str_replace($vars, "", $channelInfo['channel_name']);

      if($prefix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_busy] . " " . $channelInfo['channel_name']));
      }elseif($postfix && $busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_busy]));
      }elseif($prefix && !$busy)  {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_busy] . " " . $channelInfo['channel_name']));
      }elseif($postfix && !$busy) {
        $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name'] . " " . $config[$postfix_busy]));
      }
    }elseif($prefix && !(strstr($channelInfo['channel_name'], $config[$prefix_busy]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $config[$prefix_busy]. " " . $channelInfo['channel_name']));
    }elseif($postfix && !(strstr($channelInfo['channel_name'], $config[$postfix_busy]) !== false)) {
      $tsAdmin->channelEdit($chid, Array('channel_name' => $channelInfo['channel_name']. " " .$config[$postfix_busy]));
    }


  }
}


//*******************************************************************************************
//*********************************** Internal Functions ************************************
//*******************************************************************************************

function __construct(multibotCore $multibotCore)  {
  $this->multibotCore = $multibotCore;
}


// Koniec klasy
}
?>
