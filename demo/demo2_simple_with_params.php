<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params = array();
$params["title"]          = "A simple chat with user's parameters";
$params["nick"]           = "guest";  // setup the intitial nickname
$params["frozen_nick"]    = true;     // do not allow to change the nickname
$params["shownotice"]     = 0;        // 0 = nothing, 1 = just nickname changes, 2 = 1+connect/quit
$params["max_nick_len"]   = 10;       // nickname length could not be longer than 10 caracteres
$params["max_text_len"]   = 50;       // a message cannot be longer than 50 caracteres
$params["refresh_delay"]  = 5000;     // chat refresh speed is 5 secondes (5000ms)
$params["max_msg"]        = 15;       // max message in the history is 15 (message seen when reloading the chat)
$params["height"]         = "230px";  // height of chat area is 230px
$params["width"]          = "800px";  // width of chat area is 800px
$params["debug"]          = true;     // activate debug console
//$params["data_private"] = "/dev/shm/mychat"; // specify a special directory to write data on a tmpfs ramdisk (only work on linux)

$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <title>phpFreeChat demo</title>

    <?php $chat->printJavascript(); ?>
    <?php $chat->printStyle(); ?>

  </head>

  <body>
    <?php $chat->printChat(); ?>
  </body>
</html>
