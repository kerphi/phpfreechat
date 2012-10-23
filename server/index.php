<?php

include_once __DIR__.'/lib/Slim/Slim/Slim.php';
include_once __DIR__.'/config.php';

//Slim::registerAutoloader();
$app = new Slim();


function debug($msg) {
  if (is_string($msg)) {
    file_put_contents(__DIR__.'/log/pfc.log', $msg."\n", FILE_APPEND);
  } else {
    file_put_contents(__DIR__.'/log/pfc.log', print_r($msg, true), FILE_APPEND);
  }
}

$req = $app->request();
$res = $app->response();
$res['X-Powered-By'] = 'phpFreeChat';

// connect custom user hooks
foreach ($GLOBALS['pfc_hooks'] as $hook_name => $hooks) {
  foreach ($hooks as $priority => $function) {
    $app->hook($hook_name, $function($app, $req, $res), $priority);
  }
}

require 'routes/auth.php';
require 'routes/channels.php';
require 'routes/users.php';

$app->run();
