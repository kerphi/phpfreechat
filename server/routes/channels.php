<?php

include_once 'container/channels.php';
include_once 'container/users.php';

class Route_channels {

  public $cid = 'xxx';
  
  /**
   * 
   */
  public function get($req) {
    // 0 channels
    // 1 :cid
    // 2 users
    // 3 :uid

    // /channels/  (list available channels)
    if (!isset($req['url'][1]) or $req['url'][1] === '') {
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(array('xxx'));
      return;
    }

    $this->cid = $req['url'][1];

    // /channels/xxx/ (list available field for the channel)
    if (!isset($req['url'][2]) or $req['url'][2] === '') {
      header("HTTP/1.1 501"); // not implemented
      return;
    }

    // /channels/xxx/users/ (users connected in the channel)
    if (isset($req['url'][2]) and $req['url'][2] === 'users') {
      $rcm = new Route_channels_users($this);
      return $rcm->get($req);
    }
    
    header("HTTP/1.1 404");
  }

  /**
   * Create a new channel or
   * Post a message on a channel or
   * Join a channel
   * /channels/
   * /channels/:cid/msg/
   * /channels/:cid/users/
   */
  public function post($req) {
    // 0 channels
    // 1 :cid
    // 2 users | msg
    // 3 :uid

    // check user acces
    session_start();
    if (!isset($_SESSION['userdata']) or !isset($_SESSION['userdata']['id'])) {
      header("HTTP/1.1 401 Need to authenticate");
      return;
    }

    // /channels/  (create a new channel)
    if (!isset($req['url'][1]) or $req['url'][1] === '') {
      $cid = Container_channels::generateCid();
      
      // todo: handle channel name
      
      header("HTTP/1.1 204");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(array('id' => $cid));
      return;
    }

    $this->cid = $req['url'][1];

    // /channels/xxx/
    if (!isset($req['url'][2]) or $req['url'][2] === '') {
      header("HTTP/1.1 404");
      return;
    }

    // /channels/xxx/msg/ (post a new message on the channel)
    if (isset($req['url'][2]) and $req['url'][2] === 'msg') {
      $rcm = new Route_channels_msg($this);
      return $rcm->post($req);
    }

    // /channels/xxx/users/ (join the channel)
    if (isset($req['url'][2]) and $req['url'][2] === 'users') {
      $rcm = new Route_channels_users($this);
      return $rcm->post($req);
    }
    
    header("HTTP/1.1 404");
  }


}

/**
 * /channels/:cid/msg/
 */
class Route_channels_msg {
  
  public $rc = null;
  
  function __construct(Route_channels $rc) {
    $this->rc = $rc;
  }
  
  /**
   * Post a new message on the channel
   * /channels/:cid/msg/
   */
  public function post($req) {
    // 0 channels
    // 1 :cid
    // 2 msg

    // check that user is subscribed to the channel
    $uid = $_SESSION['userdata']['id'];
    if (!Container_channels::checkChannelUser($this->rc->cid, $uid)) {
      header("HTTP/1.1 403 You have to join channel before post a message");
      return;
    }

    // check that request content contains a message
    if (!isset($req['params']['body']) or $req['params']['body'] === '') {
      header("HTTP/1.1 400 Missing parameter [message]");
      return;
    }

    // post message
    include_once 'routes/messages.php';
    $msg = Container_messages::postMsgToChannel($this->rc->cid, $uid, $req['params']['body']);

    header("HTTP/1.1 200");
    header('Content-Type: application/json; charset=utf-8');
    echo $msg;
  }


}


/**
 * /channels/:cid/users/
 */
class Route_channels_users {
  
  public $rc = null;
  
  function __construct(Route_channels $rc) {
    $this->rc = $rc;
  }

  /**
   * List users in a channel
   * /channels/:cid/users/
   */
  public function get($req) {
    if (!isset($req['url'][3]) or $req['url'][3] === '') {
      header("HTTP/1.1 200");
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(Container_channels::getChannelUsers($this->rc->cid));
      return;
    }

    header("HTTP/1.1 501");
  }

  /**
   * User join a channel
   * /channels/:cid/users/
   */
  public function post($req) {
    // 0 channels
    // 1 :cid
    // 2 users

    $uid = $_SESSION['userdata']['id'];

    // check this user is online
    if (!Container_users::checkUser($uid)) {
      header('HTTP/1.1 400 User is not connected');
      return;
    }
    
    // todo remove this code when channel create/join will be implemented
    $cdir = Container_channels::getChannelsDir();
    $cpath = $cdir.'/'.$this->rc->cid.'/';
    @mkdir($cpath, 0777, true);
    @mkdir($cpath.'/users', 0777, true);
    
    $cupath = Container_channels::getChannelUserPath($this->rc->cid, $uid);    
    if (file_exists($cupath)) {
      header('HTTP/1.1 200 User already subscribed');
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(Container_channels::getChannelUsers($this->rc->cid, true));
      return;
    } else {
      // join the channel
      touch($cupath);
      
      // post a joind message (todo: add a 'join' type to the message)
      include_once 'routes/messages.php';
      $msg = Container_messages::postMsgToChannel($this->rc->cid, $uid, $uid.' joined Default room');
      
      header('HTTP/1.1 201 User joined the channel');
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(Container_channels::getChannelUsers($this->rc->cid, true));
      return;
    }
  }

}

