<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params = array();
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["nick"] = "guest".rand(1,1000);
$params["container_type"] = "mysql";
$params["container_cfg_mysql_host"]     = "localhost";        // default value is "localhost"
$params["container_cfg_mysql_port"]     = 3306;               // default value is 3306
$params["container_cfg_mysql_database"] = "phpfreechat";      // default value is "phpfreechat"
$params["container_cfg_mysql_table"]    = "chat";             // default value is "phpfreechat"
$params["container_cfg_mysql_username"] = "phpfreechat";      // default value is "root"
$params["container_cfg_mysql_password"] = "yX7TZbZMUyZnXp6U"; // default value is ""
$chat = new phpFreeChat($params);

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
