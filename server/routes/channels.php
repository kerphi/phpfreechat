<?php

include_once 'container/users.php';
include_once 'container/channels.php';
include_once 'container/channels-op.php';
include_once 'container/channels-ban.php';
include_once 'container/messages.php';

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

  // run garbage collector to purge old timeout message
  Container_users::runGC();

  // check this user is online
  if (!Container_users::checkUserExists($uid)) {
    $res->status(400); // User is not connected
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "User is not connected" }');
    return;
  }

  // check the user name is not banished or not on this channel
  // do not allow the join if he is banned and he is not already online
  $isban = false;
  $name   = Container_users::getUserData($uid, 'name');
  $isjoin = Container_channels::checkChannelUser($cid, $uid);
  if (!$isjoin and Container_channels_ban::isBan($cid, $name)) {
    $baninfo = Container_channels_ban::getBanInfo($cid, $name);
    $isban = true;
  }
  // check if the user is banned from this channel (from the hook)
  if (!$isban and !$isjoin and isset($GLOBALS['pfc_hooks']['pfc.isban'])) {
    foreach ($GLOBALS['pfc_hooks']['pfc.isban'] as $hook) {
      $login   = Container_users::getUserData($uid)->name;
      $channel = $cid; // todo: replace this by the channel fullname
      $baninfo = $hook($login, $channel, $uid, $cid);
      $isban = ($baninfo !== false);
    }
  }
  if ($isban) {
    $res->status(403);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40305, array('baninfo' => $baninfo))); // You have been banished from this channel
    return;
  }
  
  if (!Container_users::joinChannel($uid, $cid)) {
    $res->status(200); // User already joined the channel
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(array(
      'users' => Container_channels::getChannelUsers($cid, true),
      'op'    => Container_channels_op::getOpList($cid),      
    )));
    return;
  } else {
    // check if the user is an operator on this channel (from the hook)
    $isop = false;
    if (isset($GLOBALS['pfc_hooks']['pfc.isop'])) {
      foreach ($GLOBALS['pfc_hooks']['pfc.isop'] as $hook) {
        $login   = Container_users::getUserData($uid)->name;
        $channel = $cid; // todo: replace this by the channel fullname
        $isop = $hook($login, $channel, $uid, $cid);
      }
    }
    
    // first is op ? first connected user on the channel is an operator
    if ($GLOBALS['first_is_op'] and count(Container_channels::getChannelUsers($cid)) == 1) {
      $isop = Container_channels_op::addOp($cid, $uid);
    }
    
    if ($isop) {
      Container_channels_op::addOp($cid, $uid);
    }
    
    // post a join message
    // when a join message is sent, body contains user's data and the "op" flag
    $body = array('userdata' => Container_users::getUserData($uid),
                  'op'       => $isop);
    $msg = Container_messages::postMsgToChannel($cid, $uid, $body, 'join');
    
    $res->status(201); // User joined the channel
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(array(
      'users' => Container_channels::getChannelUsers($cid, true),
      'op'    => Container_channels_op::getOpList($cid),
    )));
    return;
  }

});

/**
 * Leave a channel
 * or is kicked from a channel
 */
$app->delete('/channels/:cid/users/:uid', function ($cid, $uid) use ($app, $req, $res) {
  
  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  $online_uid = $_SESSION['userdata']['id'];
  
  // this is a kick ?
  $isakick = ($uid !== $online_uid);

  // check if the kick is allowed
  if ($isakick) {
    // check operator rights of $online_uid
    if (!Container_channels_op::isOp($cid, $online_uid)) {
      $res->status(403);
      $res['Content-Type'] = 'application/json; charset=utf-8';
      $res->body(GetPfcError(40304)); // You are not allowed to kick because you don't have op rights
      return;
    }
  }
  // check this trargeted user is online on the channel
  if (!Container_channels::checkChannelUser($cid, $uid)) {
    $res->status(404); // User is not connected
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40401)); // User is not connected to the channel
    return;
  }

  if ($isakick) {
    // this is a kick ?
    $reason = $req->params('reason');
    
    // post a kick message
    $msg = Container_messages::postMsgToChannel($cid, $online_uid, array('target' => $uid, 'reason' => $reason), 'kick');
    
    // remove the targeted user from the channel (must be executed after postMsgToChannel)
    Container_users::leaveChannel($uid, $cid);

    // success code
    $res->status(200);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    // why not blank data ? 
    // $res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
    return;
  } else {
    // this is not a kick

    // post a leave message
    $msg = Container_messages::postMsgToChannel($cid, $online_uid, $uid, 'leave');
    
    // user leave the channel
    Container_users::leaveChannel($uid, $cid);
    
    // success code
    $res->status(200);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    // why not blank data ? 
    //$res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
    return;
  }


});

/**
 * Post a message on a channel
 */
$app->post('/channels/:cid/msg/', function ($cid) use ($app, $req, $res) {  

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  $uid = $_SESSION['userdata']['id'];
  
  // check this user is online
  if (!Container_users::checkUserExists($uid)) {
    $res->status(400); // User is not connected
    return;
  }

  // check this user has joined the channel
  if (!Container_channels::checkChannelUser($cid, $uid)) {
    $res->status(403); // You have to join channel before post a message
    return;
  }
 
  // check that request content contains a message
  $data = json_decode($req->getBody());
  if (!$data or !is_string($data)) {
    $res->status(400); // Wrong message format
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Wrong message format (must be a JSON string)" }');
    return;
  }

  // post the message
  $http_result = Container_messages::postMsgToChannel($cid, $uid, $data);
  $http_status = 201;
  
  $res->status($http_status);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body($http_result);
});
