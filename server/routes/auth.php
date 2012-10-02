<?php

include_once 'container/users.php';

$app->get('/auth', function () use ($app, $req, $res) {
  // check if a user session already exists
  session_start();
  if (isset($_SESSION['userdata']) and isset($_SESSION['userdata']['id'])) {
    
    // if user has been timeouted (network problem)
    $uid = $_SESSION['userdata']['id'];
    $uid = Container_users::generateUid($uid); // needed to recreate directories
    if (!Container_users::checkUserExists($uid)) {
      Container_users::setUserData($uid, $_SESSION['userdata']);
    }
    Container_users::setIsAlive($uid);
    
    $res->status(200); // , 'User authenticated'
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode($_SESSION['userdata']));
    return;
  } 

  // apply the user defined auth hook
  $app->applyHook('pfc.before.auth', $hr = new stdClass);
  // TODO: check if $hr->login is defined and use it to overload default login step
  
  // check if a login/password has been set
  // allow Pfc-Authorization header because Authorization can be disabled by reverse proxy
  $auth = $req->headers('Authorization') ?
    $req->headers('Authorization') :
    ($req->headers('Pfc-Authorization') ? $req->headers('Pfc-Authorization') : '');
  if (!$auth) {
    $res->status(403);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res['Pfc-WWW-Authenticate'] = 'Basic realm="Authentication"';
    $res->body('{ "error": "Need authentication" }');
    return;
  }

  // decode basic http auth header
  $auth = @explode(':', @base64_decode(@array_pop(@explode(' ', $auth))));
  if (!isset($auth[0]) && !$auth[0]) {
    $res->status(400);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Login is missing" }');
    return;
  }
  $login    = trim($auth[0]);
  $password = isset($auth[1]) ? $auth[1] : '';
  
  // check login/password
  if ($login and Container_indexes::getIndex('users/name', $login)) {
    $res->status(403);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res['Pfc-WWW-Authenticate'] = 'Basic realm="Authentication"';
    $res->body('{ "error": "Login already used" }');
    return;
  } else if ($login) {
    $uid = Container_users::generateUid();
    $udata = array(
      'id'       => $uid,
      'name'     => $login,
//      'email'    => (isset($req['params']['email']) and $req['params']['email']) ? $req['params']['email'] : (string)rand(1,10000),
      'role'     => 'user',
    );
    Container_users::setUserData($uid, $udata);
    $_SESSION['userdata'] = $udata;
    Container_users::setIsAlive($uid);
    
    $res->status(200);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body(json_encode($_SESSION['userdata']));
    return;
  } else {
    $res->status(403);
    $res['Pfc-WWW-Authenticate'] = 'Basic realm="Authentication"';
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Wrong credentials" }');    
    return;
  }

});

$app->delete('/auth', function () use ($app, $req, $res) {

  // check if session exists
  session_start();
  if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
    $res->status(200);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res->body('{ "error": "Already disconnected" }');    
    return;
  }
  
  // store userdata in a cache in order to return it later
  $ud = $_SESSION['userdata'];

  // logout
  $_SESSION['userdata'] = array();
  session_destroy();

  // return ok and the user data
  $res->status(201);
  $res['Content-Type'] = 'application/json; charset=utf-8';
  $res->body(json_encode($ud));

});
