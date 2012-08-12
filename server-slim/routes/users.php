<?php

include_once 'container/users.php';

/**
 * Returns users list
 */
$app->get('/users/', function () use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns the users list');
});

/**
 * Returns users data or 404
 */
$app->get('/users/:uid/', function ($uid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns users data '.$uid);
});

/**
 * Returns user's pending messages
 */
$app->get('/users/:uid/msg/', function ($uid) use ($app, $req, $res) {

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  if ($uid !== $_SESSION['userdata']['id']) {
    $res->status(403); // Forbidden
    return;
  }

  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(Container_users::getUserMsgs($uid, true));
});
