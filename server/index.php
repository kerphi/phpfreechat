<?php

include_once dirname(__FILE__).'/lib/Slim/Slim/Slim.php';
include_once dirname(__FILE__).'/config.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

function debug($msg) {
  if (is_string($msg)) {
    file_put_contents(dirname(__FILE__).'/log/pfc.log', $msg."\n", FILE_APPEND);
  } else {
    file_put_contents(dirname(__FILE__).'/log/pfc.log', print_r($msg, true), FILE_APPEND);
  }
}

$req = $app->request();
$res = $app->response();
$res['X-Powered-By'] = 'phpfreechat-'.$GLOBALS['pfc_version'];

// connect custom user hooks
foreach ($GLOBALS['pfc_hooks'] as $hook_name => $hooks) {
  foreach ($hooks as $priority => $function) {
    $GLOBALS['pfc_hooks'][$hook_name][$priority] = $function($app, $req, $res);
  }
}

require 'routes/auth.php';
require 'routes/channels.php';
require 'routes/users.php';

$app->run();
