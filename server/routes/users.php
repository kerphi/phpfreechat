<?php

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
      echo json_encode(Users_utils::getUsers());
      return;
    }
    
    $this->uid = $req['url'][1];

    // /users/:uid/  (user info)
    if (!isset($req['url'][2]) or $req['url'][2] === '') {
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo Users_utils::getUserInfo($this->uid, true);
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
      echo Users_utils::getUserMsgs($this->uid, true);
      return;
    }

    header("HTTP/1.1 501");
  }

  /**
   * Create a new user
   * /users/
   */
   // TODO: the /auth route create the user
   //       so POST on /users/ makes not sense
   //       but PUT on /users/:uid/ makes sense to update userdata
//   public function post($req) {
//     // check user acces
//     session_start();
//     if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
//       header("HTTP/1.1 401 Need to authenticate");
//       return;
//     }
// 
//     if (!isset($req['params']['name']) or $req['params']['name'] === '') {
//       header("HTTP/1.1 400 Missing parameter");
//       return;
//     }
// 
//     $uid = Users_utils::generateUid();
//     $udata = json_encode(array(
//       'id'    => $uid,
//       'name'  => $req['params']['name'],
//       'email' => isset($req['params']['email']) ? $req['params']['email'] : rand(1,1000).'@gmail.com',
//       'role'  => 'user',
//       'timestamp' => time(),
//     ));
//     Users_utils::setUserInfo($udata);
// 
//     header("HTTP/1.1 200");
//     header('Content-Type: application/json; charset=utf-8');
//     echo $udata;
//   }


}


class Users_utils {
  
  static public function getUsersDir() {
    $datadir = __DIR__.'/../data';
    $udir = $datadir.'/users';
    return $udir;
  }
  
  static public function getUserDir($uid) {
    $udir = self::getUsersDir();
    return $udir.'/'.$uid;
  }
  
  static public function getUserMsgDir($uid) {
    $udir = self::getUsersDir();
    return $udir.'/'.$uid.'/msg';
  }
  
  static public function generateUid() {
    $udir = self::getUsersDir();
    do {
      $uid = sha1(uniqid('', true));
      $upath = $udir.'/'.$uid;
    } while (file_exists($upath));
    @mkdir($upath, 0777, true);
    @mkdir($upath.'/msg', 0777, true);
    return $uid;
  }
  
  static public function getUserInfo($uid, $injson = false) {
    $udir = self::getUserDir($uid);
    $info = file_get_contents($udir.'/info.json'); 
    return $injson ? $info : json_decode($info);
  }

  static public function setUserInfo($info) {
    file_put_contents(Users_utils::getUserDir($info['id']).'/info.json', json_encode($info));
  }
  
  static public function getUsers() {
    $users = array();
    foreach(scandir(self::getUsersDir()) as $value) {
        if($value === '.' || $value === '..') {continue;}
        $users[] = $value;
    }
    return $users;
  }
  
  static public function getUserMsgs($uid, $injson = false) {
    $umdir = self::getUserMsgDir($uid);
    $msgs = array();
    foreach(scandir($umdir) as $value) {
        if($value === '.' || $value === '..') {continue;}
        $msgs[] = file_get_contents($umdir.'/'.$value);
        unlink($umdir.'/'.$value);
    }
    return $injson ? '['.implode(',', $msgs).']' : array_map("json_decode", $msgs);
  }

  static public function checkUser($uid) {
    return file_exists(self::getUserDir($uid));
  }

}


