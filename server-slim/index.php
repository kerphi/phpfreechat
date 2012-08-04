<?php

include_once __DIR__.'/lib/Slim/Slim/Slim.php';

$app = new Slim();
$req = $app->request();
$res = $app->response();
$res['X-Powered-By'] = 'phpFreeChat';

require 'routes/auth.php';

$app->run();
