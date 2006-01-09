<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params = array();
$params["title"]          = "A simple chat with user's parameters";
$params["nick"]           = "guest";
$params["frozen_nick"]    = true;
$params["max_nick_len"]   = 10;
$params["max_text_len"]   = 50;
$params["refresh_delay"]  = 1000;
$params["max_msg"]        = 15;
$params["height"]         = "230px";
$params["width"]          = "800px";
$params["debug"]          = true;

$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>phpFreeChat demo</title>

<?php $chat->printJavascript(); ?>
<?php $chat->printStyle(); ?>

	</head>
	<body>

<?php $chat->printChat(); ?>

	</body>
</html>