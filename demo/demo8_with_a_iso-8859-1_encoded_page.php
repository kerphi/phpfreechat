<?php

// force the encoding because off some strange configured server
header("Content-Type: text/html; charset=ISO-8859-1");

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"]        = md5(__FILE__); // calculate a unique id for this chat
$params["title"]           = "A chat with a ISO-8859-1 encoded page";
$params["language"]        = "fr_FR";
$params["nick"]            = "äöü"; // a UTF-8 encoded nickname (I know, this is not consistent with output_encoding...)
$params["output_encoding"] = "ISO-8859-1"; // same as the web page encoding
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
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
