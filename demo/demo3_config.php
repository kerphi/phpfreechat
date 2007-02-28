<?php

require_once dirname(__FILE__)."/../src/pfcglobalconfig.class.php";
$params["serverid"]           = md5(__FILE__); // calculate a unique id for this chat
$params["title"]              = "A chat with one script for client and on script for server";
$params["nick"]               = "guest";
$params["server_script_path"] = dirname(__FILE__)."/demo3_server.php";

?>