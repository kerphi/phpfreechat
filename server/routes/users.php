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
 * Returns user's data or 404
 */
$app->get('/users/:uid/', function ($uid) use ($app, $req, $res) {

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Need to authenticate" }');
    return;
  }

  if (!Container_users::checkUserExists($uid)) {
    $res->status(404);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "user $uid does not exist" }');
    return;
  }
  
  // Only allow to get userdata if connected user is in the same channel
  if ($_SESSION['userdata']['id'] != $uid) {
    $cuser1 = Container_users::getUserChannels($uid);
    $cuser2 = Container_users::getUserChannels($_SESSION['userdata']['id']);  
    if (count(array_intersect($cuser1, $cuser2)) == 0) {
      $res->status(403); // Forbidden
      $res['Content-Type'] = 'application/json; charset=utf-8';
      $res->body('{ "error": "Forbidden" }');
      return;
    }
  }

  // returns user data in json
  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(Container_users::getUserData($uid, null, true));
});

/**
 * Returns user's pending messages
 */
$app->get('/users/:uid/pending/', function ($uid) use ($app, $req, $res) {

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

  if (!Container_users::checkUserExists($uid)) {
    $res->status(404);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "user data does not exist" }');
    return;
  }

  // store that user is alive
  Container_users::setIsAlive($uid);
  // run garbage collector
  Container_users::runGC();

  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(Container_users::getUserMsgs($uid, true));
});


/**
 * Set the close flag (when a user reload or close his window)
 */
$app->put('/users/:uid/closed', function ($uid) use ($app, $req, $res) {

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

  if (!Container_users::checkUserExists($uid)) {
    $res->status(404);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "user data does not exist" }');
    return;
  }

  // set the close flag
  Container_users::setCloseFlag($uid);

  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('1');
});

