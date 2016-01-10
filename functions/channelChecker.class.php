<?php
class channelChecker extends start {
//*******************************************************************************************
//****************************************** Vars *******************************************
//*******************************************************************************************
  protected $start_timer = 0;
  private $multibotCore;
  private $config;
  protected $function_name = "Channel Checker";
//*******************************************************************************************
//************************************ Public Functions *************************************
//******************************************************************************************
function start_function() {
  $tsAdmin = $this->multibotCore->getTsAdmin();
  $multibotCore = $this->multibotCore;
  $config = $this->multibotCore->getConfig("multibot");
  $config = $config['channelchecker']['channelchecker'];
  $paths = $this->multibotCore->getPaths();
  foreach($config as $id => $section) {
    if(is_int($id))  {
      $type_all = false;
      $type_group = false;
      if(isset($config[$id . "_user_type"]))  {
        if($config[$id . "_user_type"] == "all")  {
          $type_all = true;
        }elseif($config[$id . "_user_type"] == "channel_group"){
          $type_group = true;
        }else {
          $multibotCore->addError("(channelChecker) Podano nieprawidłowy typ uzytkownika (". $id . "_user_type)");
          return false;
        }
      }else {
        if($config["user_type"] == "all")  {
          $type_all = true;
        }elseif($config["user_type"] == "channel_group"){
          print "tutaj";
          $type_group = true;
        }else {
          $multibotCore->addError("(channelChecker) Podano nieprawidłowy typ uzytkownika (user_type)");
          return false;
        }
      }
      if(isset($config[$id . "_channel_group"])) {
        $channel_group = $config[$id . "_channel_group"];
      }else {
        $channel_group = $config["channel_group"];
      }
      if(isset($config[$id . "_time_to_write"])) {
        $time_to_write = $config[$id . "_time_to_write"];
      }else {
        $time_to_write = $config["time_to_write"];
      }
      $time_to_write *= 3600;
      if(isset($config[$id . "_time_extend"]))  {
        $time_extend = $config[$id . "_time_extend"];
      }else {
        $time_extend = $config["time_extend"];
      }
      $time_extend *= 3600;
      $channelList = $this->multibotCore->getChannelList();
      $section = explode(",", $config[$id]);
      if(!(count($section) >= 2)) {
        return false;
      }
      $in_section = false;
      $channelStats = parse_ini_file($paths['folders']['configs'] . "channelChecker.ch", true);
      foreach($channelList as $channel)  {
        if($channel['cid'] == $section[1])  {
          $in_section = false;
          break;
        }elseif(($channel['channel_order'] == $section[0]) || $in_section)  {
          $in_section = true;
          if(($channel['pid'] != 0) && !$config['subchannels']) {
            continue;
          }
          if(!isset($channelStats[$channel['cid']]))  {
            $channelStats[$channel['cid']] = Array('scan_date' => date("d.m.y,H:i:s"), 'expire_date' => date("d.m.y,H:i:s", time() + $time_extend), 'write' => '2', 'time_to_write' => date("d.m.y,H:i:s"));
          }
          if(($channel['total_clients'] != 0) && $type_all) {
            $channelStats[$channel['cid']]['expire_date'] = date("d.m.y,H:i:s", time() + $time_extend);
            $channelStats[$channel['cid']]['scan_date'] = date("d.m.y,H:i:s");
          }elseif(($channel['total_clients'] != 0) && $type_group && $this->usersOnChannelWithChannelGroup($channel_group, $channel['cid'])) {
            print 'tutaj';
            $channelStats[$channel['cid']]['expire_date'] = date("d.m.y,H:i:s", time() + $time_extend);
            $channelStats[$channel['cid']]['scan_date'] = date("d.m.y,H:i:s");
          }elseif(($channel['total_clients'] == 0) && ($channelStats[$channel['cid']]['expire_date'] <= date("d.m.y,H:i:s")))  {
            $status = $tsAdmin->channelDelete($channel['cid']);
            if($status['success'])  {
              unset($channelStats[$channel['cid']]);
              continue;
            }else {
              $this->multibotCore->addError("Nie moża usunąć kanału o id=".$channel['cid'], true);
            }
          }
          if(($channelStats[$channel['cid']]['time_to_write'] <= date("d.m.y,H:i:s")) || ($channelStats[$channel['cid']]['write'] == "2"))  {
            $channelInfo = $tsAdmin->channelInfo($channel['cid']);
            $varsMessage = Array(
            "{expire_date}",
            "{last_scan}",
            "{time_extension}"
            );
            $a = $time_extend;
            $time_extendinfo = $a/3600;
            $valuesMessage = Array(
            str_replace(",", " ", $channelStats[$channel['cid']]['expire_date']),
            str_replace(",", " ", $channelStats[$channel['cid']]['scan_date']),
            $time_extendinfo." h"
            );
            $sourceMessage = file_get_contents($paths['folders']['functions-configs'] . $config['message']);
            $message = str_replace($varsMessage,$valuesMessage,$sourceMessage);
            $message = "Powiadomienie (channelChecker)\n" .  $message;
            if(empty($channelInfo['data']['channel_description']))  {
              $tsAdmin->channelEdit($channel['cid'], Array('channel_description' => $message));
            }elseif(strstr($channelInfo['data']['channel_description'], "Powiadomienie (channelChecker)") !== false) {
              $tsAdmin->channelEdit($channel['cid'], Array('channel_description' => str_replace(strstr($channelInfo['data']['channel_description'], "Powiadomienie (channelChecker)"), $message, $channelInfo['data']['channel_description'])));
            }else {
              $tsAdmin->channelEdit($channel['cid'], Array('channel_description' => $channelInfo['data']['channel_description'] . "\n" . $message));
            }
            $channelStats[$channel['cid']]['write'] = "1";
            $channelStats[$channel['cid']]['time_to_write'] = date("d.m.y,H:i:s", time() + $time_to_write);
          }
        }
        $this->writeInfo($paths['folders']['configs'] . "channelChecker.ch", $channelStats);
      }
    }
  }
}
function usersOnChannelWithChannelGroup($groupId, $channel) {
  $clientList = $this->multibotCore->getClientList();
  foreach($clientList as $client)  {
    if(($client['cid'] = $channel) && ($client["client_channel_group_id"] == $groupId))  {
      return true;
    }
  }
}
function writeInfo($sourceFile, $content) {
  $file = fopen($sourceFile, "w");
  foreach($content as $id => $value)  {
    fputs($file, "[".$id."]\n");
    foreach($value as $idVal => $vars)  {
      fputs($file, $idVal . " = " . $vars . "\n");
    }
  }
  fclose($file);
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