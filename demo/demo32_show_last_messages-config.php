<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params["title"]    = "Show last posted messages channel";
$params["serverid"] = md5($params["title"]); // calculate a unique id for this chat
$params["max_msg"]  = 20;
$params["channels"] = array("channel1");
$pfc_config =& pfcGlobalConfig::Instance( $params );

?>