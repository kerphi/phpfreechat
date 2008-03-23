<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params["serverid"]      = md5(__FILE__); // calculate a unique id for this chat
$params["language"]      = "oc_FR";
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
<?php /* start hide */ ?>
<?php
  echo "<h2>The source code</h2>";
  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  $content = preg_replace('/\<\?php \/\* start hide \*\/ \?\>.*?\<\?php \/\* end hide \*\/ \?\>/s','',$content);
  highlight_string($content);
  echo "</pre>";
?>
<?php /* end hide */ ?>
  </body>
</html>
