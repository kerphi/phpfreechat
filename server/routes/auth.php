<?php

include_once 'container/users.php';

$app->get('/auth', function () use ($app, $req, $res) {
  // run garbage collector
  Container_users::runGC();

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

  // check if the login is defined by the hook
  $login = '';
  if (isset($GLOBALS['pfc_hooks']['pfc.before.auth'])) {
    foreach ($GLOBALS['pfc_hooks']['pfc.before.auth'] as $hook) {
      $login = trim($hook());
    }
  }
  
  // check if the hook want to redirect the response
  if ($res->isRedirection()) {
    return;
  }
  
  // check if a login/password has been set
  // allow Pfc-Authorization header because Authorization can be disabled by reverse proxy
  $auth = $req->headers('Authorization') ?
    $req->headers('Authorization') :
    ($req->headers('Pfc-Authorization') ? $req->headers('Pfc-Authorization') : '');
  if (!$auth and $login == '') {
    $res->status(403);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res['Pfc-WWW-Authenticate'] = 'Basic realm="Authentication"';
    $res->body(GetPfcError(40301)); // "Need authentication"
    return;
  }
  if ($auth) {
    // decode basic http auth header
    $auth = @explode(':', @base64_decode(@array_pop(@explode(' ', $auth))));
    if (!isset($auth[0]) || trim($auth[0]) == '') {
      $res->status(400);
      $res['Content-Type'] = 'application/json; charset=utf-8';
      $res->body('{ "error": "Login is missing" }');
      return;
    }
    $login    = trim($auth[0]);
    $password = isset($auth[1]) ? $auth[1] : '';
  }
  
  // filter login with hooks
  if (isset($GLOBALS['pfc_hooks']['pfc.filter.login'])) {
    foreach ($GLOBALS['pfc_hooks']['pfc.filter.login'] as $filter) {
      $login = trim($filter($login));
    }
    if ($login == '') {
      $res->status(400);
      $res['Content-Type'] = 'application/json; charset=utf-8';
      $res->body('{ "error": "Bad characters used in login" }');
      return;
    }
  }

  // check login/password
  if ($login and Container_indexes::getIndex('users/name', $login)) {
    $res->status(403);
    $res['Content-Type'] = 'application/json; charset=utf-8';
    $res['Pfc-WWW-Authenticate'] = 'Basic realm="Authentication"';
    $res->body(GetPfcError(40302)); // "Login already used"
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
    $res->body(GetPfcError(40303)); // "Wrong credentials"
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
