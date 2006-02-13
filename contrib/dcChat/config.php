<?php

require_once dirname(__FILE__)."/phpfreechat/src/phpfreechattools.class.php";
require_once dirname(__FILE__)."/phpfreechat/src/phpfreechat.class.php";

function &getChat($room)
{
  static $chat;
  if (!isset($chat))
  {
    $params = array();
    $params["title"]         = "";
    $params["channel"]       = $room;
    $params["server_script_url"] = "ecrire/tools/dcchat/server_script.php?room=".$room;
    $params["debug"] = true;
    $chat = new phpFreeChat($params);
  }
  return $chat;
}

?>
