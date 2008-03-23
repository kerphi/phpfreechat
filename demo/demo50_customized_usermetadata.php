<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"]    = md5(__FILE__); // calculate a unique id for this chat
$params["title"]       = "A chat which shows how to use user metadata : add avatar (images) to each connected users";
$params["nick"]        = "guest".rand(1,1000);
$params["nickmeta"]    = array("avatar" => "demo50_data/avatar".rand(1,9).".jpg");
$params["theme_path"]  = dirname(__FILE__)."/demo50_data";
$params["theme"]       = "mytheme";
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>phpFreeChat demo</title>
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
  // print the current file
  echo "<h2>The customized javascript</h2>";
  $filename = dirname(__FILE__).'/demo50_data/mytheme/customize.js.php';
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>

  </body>
</html>