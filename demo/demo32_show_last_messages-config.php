<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["title"]    = "Show last posted messages channel";
$params["max_msg"]    = 1;
$params["debugxajax"]    = true;
$pfc_config =& phpFreeChatConfig::Instance( $params );

?>