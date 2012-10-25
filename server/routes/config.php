<?php

/**
 * Checks server configuration and returns status
 */
$app->get('/config', function () use ($app, $req, $res) {
  $status = array();
  
  // check that server/data/ folder is writable
  if (!is_writable(dirname(__FILE__).'/../data/')) {
    $status[] = basename(dirname(dirname(dirname(__FILE__)))).'/server/data/ folder is not writable. Please adjust folder permissions to 777.';
  }

  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($status));
});
