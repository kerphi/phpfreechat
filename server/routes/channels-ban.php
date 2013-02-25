<?php

include_once 'container/users.php';
include_once 'container/messages.php';
include_once 'container/channels-op.php';
include_once 'container/channels-ban.php';

/**
 * Returns the :cid channel banished list (list of :name)
 */
$app->get('/channels/:cid/ban/', function ($cid) use ($app, $req, $res) {  

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
  
  // get the banished names on $cid
  $bans = Container_channels_ban::getBanList($cid);
  
  $res->status(200);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($bans, JSON_FORCE_OBJECT));
});

/**
 * Adds :name64 to the :cid channel banished list
 */
$app->put('/channels/:cid/ban/:name64', function ($cid, $name64) use ($app, $req, $res) {  

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
  
  // check this user is an operator on this channel
  if (!Container_channels_op::isOp($cid, $online_uid)) {
    $res->status(403); // You have to be an operator to banish a user
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(GetPfcError(40306)) ; // You have to be an operator to banish a user
    return;
  }
  
  // add name64 to the ban list
  $opname = Container_users::getUserData($online_uid, 'name');
  $reason = $req->params('reason');
  $ok = Container_channels_ban::addBan($cid, $name64, array('opname' => $opname, 'reason' => $reason ? $reason : '', 'timestamp' => time()));
  if ($ok) {
    $name      = base64_decode($name64);
    $banuid    = Container_indexes::getIndex('users/name', $name);
    $iskickban = ($req->params('kickban') && $banuid);
    
    // notification to other connected user of this ban
    Container_messages::postMsgToChannel(
      $cid,
      $online_uid,
      array('opname'  => $opname,
            'name'    => $name,
            'reason'  => $reason ? $reason : '',
            'kickban' => $iskickban),
      'ban');

    // kick the user from the channel
    // (warning: do it after the above notification
    //  or he will not receive the notification)
    if ($iskickban) {
      Container_users::leaveChannel($banuid, $cid);
    }
    
    $res->status(201);
  } else {
    $res->status(500);
  }
});


/**
 * Remove :name64 from the :cid channel banished list
 */
$app->delete('/channels/:cid/ban/:name64', function ($cid, $name64) use ($app, $req, $res) {  

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
  
  // check this user is an operator on this channel
  if (!Container_channels_op::isOp($cid, $online_uid)) {
    $res->status(403); // You have to be an operator to unban a user
    return;
  }
  
  // removes name64 from the ban list
  $banlist = Container_channels_ban::getBanList($cid);
  $ok      = Container_channels_ban::rmBan($cid, $name64);
  if ($ok) {
  
    // check if the user is in the ban list
    $name = base64_decode($name64);
    if (!isset($banlist[$name])) {
      $res->status(404);
      return;
    }
    
    // notification to other connected user of this removed ban
    $unban_body = $banlist[$name];
    $unban_body = array_merge($unban_body, array('name' => $name));
    Container_messages::postMsgToChannel($cid, $online_uid, $unban_body, 'unban');

    $res->status(200);    
  } else {
    $res->status(500);
  }
    
});