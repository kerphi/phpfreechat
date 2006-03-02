<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$params["container_type"] = "Memory";
$params["container_cfg_sm_type"] = "auto"; // autodetect the best possible storage method
/**
Other (tested) storage types:
Eaccelerator
File // Plain text
Shmop

Not tested but available:
Systemv // System V
Mmcache  // Turck MMCache
Memcache // Memcached
Apc // APC
Apachenote // Apache note
Sqlite // does not work at the moment
Sharedance // Sharedance
*/

/*
// Use these parameters to force using the file storage
$params["container_cfg_sm_type"] = "File";
$params["container_cfg_sm_options"] = array("tmp"=>"/tmp");
*/

/*
// works not yet! (PEAR class error)
// you have to create the needed tables before using them
$params["container_cfg_sm_type"] = "Sqlite";
$params["container_cfg_sm_options"] = array(
		'db' => ':memory:',
		'table'  => 'sharedmemory',
		'var' => 'key',
		'value' => 'data',
		'persistent' => false
);
*/

/**
For other parameters/options look into the documentation of the pear class System/SharedMemory. 
*/

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
  echo htmlentities($content);
  echo "</pre>";
?>
  </body>
  
</html>