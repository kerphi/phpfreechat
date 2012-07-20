<?php

class Route_auth {

  /**
   * Used to authenticate a user
   */
  public function get($req) {

    // check if a user session already exists
    session_start();
    if (isset($_SESSION['userdata']) and isset($_SESSION['userdata']['id'])) {
      header("HTTP/1.1 200 User authenticated");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($_SESSION['userdata']);
      return;
    } 

    // check if a login/password has been set
    if (!isset($req['headers']['Authorization'])) {
      header('HTTP/1.1 403');
      header('WWW-Authenticate: Basic realm="Authentication"');
      return;
    }

    // decode basic http auth header
    $auth = @explode(':', @base64_decode(@array_pop(@explode(' ', $req['headers']['Authorization']))));
    if (!isset($auth[0]) && !$auth[0]) {
      header("HTTP/1.1 400 Login is missing");
      return;
    }
    $login    = $auth[0];
    $password = isset($auth[1]) ? $auth[1] : '';
    
    // check login/password
    if ($login == 'kerphi' and $password == 'kerphi') {
      $_SESSION['userdata'] = array(
        'id'       => 1,
        'nickname' => 'kerphi',
        'email'    => 'stephane.gully@gmail.com',
      );
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($_SESSION['userdata']);
      return;
    } else {
      header('HTTP/1.1 403 Wrong credentials');
      header('WWW-Authenticate: Basic realm="Authentication"');         
      return;
    }

  }

  /**
   * Used to logout
   */
  public function delete($req) {
    // check if session exists
    session_start();
    if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
      header("HTTP/1.1 200 Already disconnected");
      return;
    }
    
    // store userdata in a cache in order to return it later
    $cache = $_SESSION['userdata'];
    
    // logout
    session_destroy();
    
    // return ok and the user data
    header("HTTP/1.1 204 Disconnected");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($userdata);
  }
}