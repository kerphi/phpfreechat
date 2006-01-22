<?php

//file_put_contents("/tmp/config", var_export($_GET, true)."\n");

require_once dirname(__FILE__)."/config.php";
$chat =& getChat(isset($_GET["room"]) ? $_GET["room"] : "");

?>
