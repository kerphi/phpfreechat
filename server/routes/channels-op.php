<?php

include_once 'container/users.php';
include_once 'container/channels-op.php';

/**
 * Returns the :cid channel operators list (list of :uid)
 */
$app->get('/channels/:cid/op/', function ($cid) use ($app, $req, $res) {  

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
    $res->status(403); // You have to join channel
    return;
  }
  
  // get the operators on $cid
  $ops = Container_channels_op::getOpList($cid);
  
  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($ops));
});

/**
 * Tells if :uid is operator on :cid
 */
$app->get('/channels/:cid/op/:uid', function ($cid, $uid) use ($app, $req, $res) {  

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  $online_uid = $_SESSION['userdata']['id'];

  // check this user is online
  if (!Container_users::checkUserExists($online_uid)) {
    $res->status(400); // User is not connected
    return;
  }

  // check this user has joined the channel
  if (!Container_channels::checkChannelUser($cid, $online_uid)) {
    $res->status(403); // You have to join channel
    return;
  }
  
  // check if requested $uid is op on $cid
  $op = Container_channels_op::isOp($cid, $uid);
  if ($op) {
    $res->status(200);
  } else {
    $res->status(404);
  }
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($op));
});


/**
 * Adds :uid to the operator list on :cid channel
 * (only operators are allowed to add another op)
 */
$app->put('/channels/:cid/op/:uid', function ($cid, $uid) use ($app, $req, $res) {  

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  $online_uid = $_SESSION['userdata']['id'];

  // check this user is online
  if (!Container_users::checkUserExists($online_uid)) {
    $res->status(400); // User is not connected
    return;
  }

  // check this user is an operator on this channel
  if (!Container_channels_op::isOp($cid, $online_uid)) {
    $res->status(403); // You have to be an operator to give op to other user
    return;
  }

  // check the new operator in online on this channel
  if (!Container_channels::checkChannelUser($cid, $uid)) {
    $res->status(404); 
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40401)) ; // User is not connected to the channel
    return;
  }

  // check the new operator is not yet an operator on this channel
  if (Container_channels_op::isOp($cid, $uid)) {
    $res->status(400);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40003)); // User is already an operator on this channel
    return;
  }

  // add $uid user as a $cid channel operator
  $ok = Container_channels_op::addOp($cid, $uid);
  if ($ok) {
    $res->status(200);
    // notification to other connected user of this new operator
    Container_messages::postMsgToChannel($cid, $online_uid, $uid, 'op');
  } else {
    $res->status(500);
  }
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($ok));
});

/**
 * Removes :uid from the operator list on :cid channel
 * (only operators are allowed to remove other op)
 */
$app->delete('/channels/:cid/op/:uid', function ($cid, $uid) use ($app, $req, $res) {  

  // check user acces
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(401); // Need to authenticate
    return;
  }
  $online_uid = $_SESSION['userdata']['id'];

  // check this user is online
  if (!Container_users::checkUserExists($online_uid)) {
    $res->status(400); // User is not connected
    return;
  }

  // check this user is an operator on this channel
  if (!Container_channels_op::isOp($cid, $online_uid)) {
    $res->status(403); // You have to be an operator to give op to other user
    return;
  }

  // check the destination user is an operator on this channel
  if (!Container_channels_op::isOp($cid, $uid)) {
    $res->status(400);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40002)); // This user is not an operator on the channel
    return;
  }
  
  // removes $uid user as a $cid channel operator
  $ok = Container_channels_op::rmOp($cid, $uid);
  if ($ok) {
    $res->status(200);
    // notification to other connected user of this removed operator
    Container_messages::postMsgToChannel($cid, $online_uid, $uid, 'deop');
  } else {
    $res->status(500);
  }
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($ok));
});

