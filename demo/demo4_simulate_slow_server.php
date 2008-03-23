<?php

require_once "../src/phpfreechat.class.php";

// sleep [1-5] seconds to simulate a random serveur lag
sleep(rand(1,5));

$params["serverid"]      = md5(__FILE__); // calculate a unique id for this chat
$params["title"]         = "A chat simulating slow server (lag form 1 to 5 seconds)";
$params["nick"]          = "guest";
$params["refresh_delay"] = 2000;
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

  </body>
</html>