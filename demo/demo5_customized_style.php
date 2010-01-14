<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"]      = md5(__FILE__); // calculate a unique id for this chat
$params["title"]         = "A chat with a customized stylesheet";
$params["height"]        = "500px";
// do not uses width parameter because of a display bug in IE6
//$params["width"]         = "650px";
$params["max_msg"]       = 21;
$params["theme_path"]    = dirname(__FILE__)."/demo5_customized_style_data";
$params["theme"]         = "mytheme";
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
  
  <div style="width: 650px; margin: auto;">
    <?php $chat->printChat(); ?>
  </div>

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
  $filename = dirname(__FILE__)."/demo5_customized_style_data/mytheme/style.css.php";
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>

  </body>
</html>
