<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

// calculate a unique id for this chat,
// this id must be used by pfcInfo to get chat infos
$params["serverid"] = md5("Whois online demo");
$params["title"]    = "Whois online demo";

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
  <p>Then have a look to the <a href="./demo31_show_who_is_online-whoisonline.php">whoisonline script</a></p>
  
<?php
  // print the current file
  echo "<h2>The source code</h2>";

  $filename = dirname(__FILE__)."/demo31_show_who_is_online-config.php";
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";

  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>
  </body>
  
</html>
