<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params = array();
$params["serverid"]       = md5(__FILE__); // calculate a unique id for this chat
$params["title"]          = "A chat with a hardcoded urls";
$params["nick"]           = "guest";  // setup the intitial nickname

// setup urls
$params["data_public_url"]   = "../data/public";
$params["server_script_url"] = "./demo21_with_hardcoded_urls.php";
$params["theme_default_url"] = "../themes";

// setup paths
$params["container_type"]         = "File";
$params["container_cfg_chat_dir"] = dirname(__FILE__)."/../data/private/chat";

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
