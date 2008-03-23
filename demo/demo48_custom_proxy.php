<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["skip_proxies"] = array("censor"); // shows how to disable a proxy (try to write fuck in the chat, it should be possible)
$params["post_proxies"] = array("myproxy");
$params["proxies_path"] = dirname(__FILE__).'/demo48_custom_proxy';
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
    <p>This demo shows how to create your own command proxy. Each chained proxies receive every transmitted commands. Your proxy can block the command, alterate the command, store some statistics. For example: to write a bot, just write a proxy.</p>
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

  </body>
</html>
