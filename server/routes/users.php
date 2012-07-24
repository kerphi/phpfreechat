<?php

include_once 'container/users.php';
class Route_users {

  public $uid = null;
  
  /**
   * List users or
   * Read user info or
   * Read pending messages of a user
   * /users/
   * /users/:uid/
   * /users/:uid/msg/
   */
  public function get($req) {
    // 0 users
    // 1 :uid
    // 2 msg

    // /users/  (list users)
    if (!isset($req['url'][1]) or $req['url'][1] === '') {
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(Container_users::getUsers());
      return;
    }
    
    $this->uid = $req['url'][1];

    // /users/:uid/  (user info)
    if (!isset($req['url'][2]) or $req['url'][2] === '') {
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo Container_users::getUserData($this->uid, null, true);
      return;
    }
  
    // /users/:uid/msg/ (pending messages)
    if (isset($req['url'][2]) and $req['url'][2] === 'msg') {

      // check user acces
      session_start();
      if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
        header("HTTP/1.1 401 Need to authenticate");
        return;
      } else if ($_SESSION['userdata']['id'] !== $this->uid) {
        header("HTTP/1.1 403");
        return;
      }

      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo Container_users::getUserMsgs($this->uid, true);
      return;
    }

    header("HTTP/1.1 501");
  }

}

