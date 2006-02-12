<?php

require_once "../src/phpfreechat.class.php";

// sleep [1-5] seconds to simulate a random serveur lag
sleep(rand(1,5));

$params = array("title" => "A chat simulating slow server (lag form 1 to 5 seconds)",
                "nick" => "guest",
                "refresh_delay" => 2000, // a fast refresh rate
                //"debug" => true,
                );
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
  </body>
</html>