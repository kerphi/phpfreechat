<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params = array();
$params["serverid"]       = md5(__FILE__); // calculate a unique id for this chat
$params["title"]          = "A simple chat with user's parameters";
$params["nick"]           = "guest";  // setup the intitial nickname
$params["frozen_nick"]    = true;     // do not allow to change the nickname
$params["shownotice"]     = 0;        // 0 = nothing, 1 = just nickname changes, 2 = connect/quit, 3 = nick + connect/quit
$params["max_nick_len"]   = 20;       // nickname length could not be longer than 10 caracteres
$params["max_text_len"]   = 300;      // a message cannot be longer than 50 caracteres
$params["max_channels"]   = 3;        // limit the number of joined channels tab to 3
$params["max_privmsg"]    = 1;        // limit the number of private message tab to 1
$params["refresh_delay"]  = 10000;    // chat refresh speed is 10 secondes (10000ms)
$params["max_msg"]        = 15;       // max message in the history is 15 (message seen when reloading the chat)
$params["height"]         = "230px";  // height of chat area is 230px
$params["debug"]          = true;     // activate debug console
$params["connect_at_startup"] = false;
$params["start_minimized"]    = true;
$params["nickmarker"]     = false;
$params["clock"]          = false;
//$params["data_private_path"] = "/dev/shm/mychat"; // specify a special directory to write data on a tmpfs ramdisk (only work on linux)

$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>phpFreeChat demo</title>

    <?php $chat->printJavascript(); ?>
    <?php $chat->printStyle(); ?>

  </head>

  <body>

  <?php $chat->printChat(); ?>

<?php
  // print the current file
  echo "<h2>The source code</h2>";
  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>

<?php
  echo "<h2>Debug</h2>";
  echo "<pre>";
  $c =& pfcGlobalConfig::Instance();
  print_r($c);
  print_r($_SERVER);
  echo "</pre>";
?>

  </body>
</html>
