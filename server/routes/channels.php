<?php

include_once 'container/users.php';
include_once 'container/channels.php';
include_once 'container/channels-op.php';
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
  
  if (!Container_users::joinChannel($uid, $cid)) {
    $res->status(200); // User already joined the channel
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(array(
      'users' => Container_channels::getChannelUsers($cid, true),
      'op'    => Container_channels_op::getOpList($cid),      
    )));
    return;
  } else {
    // post a join message
    $msg = Container_messages::postMsgToChannel($cid, $uid, null, 'join');
    
    // first is op ? first connected user on the channel is an operator
    if ($GLOBALS['first_is_op'] and count(Container_channels::getChannelUsers($cid)) == 1) {
      Container_channels_op::addOp($cid, $uid);
    }
    
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
 */
$app->delete('/channels/:cid/users/:uid', function ($cid, $uid) use ($app, $req, $res) {

  
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


  if (!Container_users::leaveChannel($uid, $cid)) {
    $res->status(404);
    // $res['Content-Type'] = 'application/json; charset=utf-8';
    // $res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
    return;
  } else {
    // post a leave message
    $msg = Container_messages::postMsgToChannel($cid, $uid, null, 'leave');
    
    $res->status(200);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode(Container_channels::getChannelUsers($cid, true)));
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
  if (!$data or !is_array($data) or count($data) < 2) {
    $res->status(400); // Wrong body format
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Wrong body format (must be a JSON array)" }');
    return;
  }

  // get the message type (first element of the array)
  $msg_type = $data[0];
  // and execute the corresponding command
  switch ($msg_type) {
    
    // post a simple message
    case 'msg':
        $http_result = Container_messages::postMsgToChannel($cid, $uid, $data[1]);
        $http_status = 201;
        break;
      
    // kick a user
    case 'kick':
        // todo
      
    default:
        debug("todo: implement the '$msg_type' command");
        $http_result = '';
        $http_status = 501;
        break;
  }
  
  $res->status($http_status);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body($http_result);
});
