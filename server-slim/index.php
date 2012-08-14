<?php

include_once __DIR__.'/lib/Slim/Slim/Slim.php';
include_once __DIR__.'/config.php';

$app = new Slim();

function debug($msg) {
  if (is_string($msg)) {
    file_put_contents(__DIR__.'/logs/pfc.log', $msg."\n", FILE_APPEND);
  } else {
    file_put_contents(__DIR__.'/logs/pfc.log', print_r($msg, true), FILE_APPEND);
  }
}

$req = $app->request();
$res = $app->response();
$res['X-Powered-By'] = 'phpFreeChat';

require 'routes/auth.php';
require 'routes/channels.php';
require 'routes/users.php';

$app->run();
