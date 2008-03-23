<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"]      = md5(__FILE__); // calculate a unique id for this chat
$params["nick"]          = "guest".rand(1,10);  // setup the intitial nickname
$params["title"]         = "A chat with a customized nickname color list";
$params["nickname_colorlist"] = array('#FF0000','#00FF00','#0000FF');
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
  $filename = dirname(__FILE__)."/demo43_change_the_nicknames_colors/mytheme/templates/pfcclient-custo.js.tpl.php";
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>

  </body>
</html>