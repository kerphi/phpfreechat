<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["title"]    = "Whois online demo channel";
$pfc_config =& pfcGlobalConfig::Instance( $params );

?>
