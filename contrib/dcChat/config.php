<?php

require_once dirname(__FILE__)."/phpfreechat/src/pfctools.php";
require_once dirname(__FILE__)."/phpfreechat/src/phpfreechat.class.php";

function &getChat($room)
{
  static $chat;
  if (!isset($chat))
  {
    $params = array();
    $params["serverid"]      = md5(__FILE__);
    $params["title"]         = "";
    $params["channel"]       = $room;
    //    $params["connect_at_startup"] = false;
    //    $params["start_minimized"]    = true;

    //echo "<pre>"; print_r($GLOBALS['news']);    echo "</pre>"; 
    $url = $GLOBALS['theme_uri']."ecrire/tools/dcchat";
    echo $url;
    $params["server_script_url"]  = $url."/server_script.php?room=".$room;
    // setup urls
    $params["data_public_url"]   = $url."/phpfreechat/data/public";
    //$params["client_script_url"] = "./demo21_with_hardcoded_urls.php";
    $params["themeurl"]          = $url."/phpfreechat/themes";
    $params["themeurl_default"]  = $url."/phpfreechat/themes";

//    $params["smileyurl"]          = "ecrire/tools/dcchat/phpfreechat/smileys";
//    $params["debug"]             = true;
    $chat = new phpFreeChat($params);
  }
  return $chat;
}

?>
