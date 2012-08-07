<?php

include_once 'container/users.php';
include_once 'container/channels.php';

/**
 * Returns channel list
 */
$app->get('/channels/', function () use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns the channel list');
});

/**
 * Returns channel data or 404
 */
$app->get('/channels/:cid/', function ($cid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('returns channel data of '.$cid);
});

/**
 * List users on a channel
 */
$app->get('/channels/:cid/users/', function ($cid) use ($app, $req, $res) {
  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode(Container_channels::getChannelUsers($cid)));
});

/**
 * Join a channel
 */
$app->put('/channels/:cid/users/:uid', function ($cid, $uid) use ($app, $req, $res) {
  
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

  // check this user is online
  if (!Container_users::checkUserExists($uid)) {
    $res->status(400); // User is not connected
    return;
  }
  
  // todo remove this code when channel create/join will be implemented
  $cdir = Container_channels::getChannelsDir();
  $cpath = $cdir.'/'.$cid.'/';
  @mkdir($cpath, 0777, true);
  @mkdir($cpath.'/users', 0777, true);
  
  if (!Container_users::joinChannel($uid, $cid)) {
    $res->status(200); // header('HTTP/1.1 200 User already subscribed');
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
    return;
  } else {
    // post a join message
    $msg = Container_messages::postMsgToChannel($cid, $uid, null, 'join');
    
    $res->status(201); //    header('HTTP/1.1 201 User joined the channel');
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
    return;
  }

});

/**
 * Leave a channel
 */
$app->delete('/channels/:cid/users/:uid', function ($cid, $uid) use ($app, $req, $res) {
  
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body($uid.' leave channel '.$cid);

});

/**
 * Post a message on a channel
 */
$app->post('/channels/:cid/msg/', function ($cid) use ($app, $req, $res) {
  $res->status(501);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body('post a messages on a channel '.$cid);
});