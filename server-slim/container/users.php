<?php

include_once 'container/indexes.php';

class Container_users {
  
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
    return $udir.'/'.$uid.'/messages';
  }
  
  static public function generateUid() {
    $udir = self::getUsersDir();
    do {
      $uid = sha1(uniqid('', true));
      $upath = $udir.'/'.$uid;
    } while (file_exists($upath));
    @mkdir($upath, 0777, true);
    @mkdir($upath.'/messages', 0777, true);
    @mkdir($upath.'/channels', 0777, true);
    return $uid;
  }
  
  /**
   * Get user's data
   * Can be called to read only one field
   * If $injson is true, data are returned json encoded
   * getUserData('xxxx', 'email');
   * getUserData('xxxx', null, true);
   */
  static public function getUserData($uid, $field = null, $injson = false) {
    $udir = self::getUserDir($uid);
    if ($field) {
      $data = file_get_contents($udir.'/'.$field);
      $data = $injson ? json_encode($data) : $data;
    } else {
      $data = file_get_contents($udir.'/index.json'); 
      $data = $injson ? $data : json_decode($data);
    }
    return $data;
  }

  /**
   * Set user's data
   * Can be called to update only one field
   * setUserData('xxxx', array('email', 'xxx@ss.com'));
   */
  static public function setUserData($uid, $userdata) {
    // create or update the index
    if (isset($userdata['name'])) {
      if (self::checkUser($uid)) {
        Container_indexes::rmIndex('users/name', self::getUserData($uid, 'name'));
      }
      Container_indexes::setIndex('users/name', $userdata['name'], $uid);
    }
  
    // write user data on disk
    $ignore_keys = array('.', '..', 'index.json', 'messages', 'channels', 'id');
    $udir = self::getUserDir($uid);
    foreach($userdata as $k => $v) {
      if (in_array($k, $ignore_keys)) {
        continue;
      }
      file_put_contents($udir.'/'.$k, $v);
    }
    $ud = array();
    foreach(scandir($udir) as $v) {
      if (in_array($v, $ignore_keys)) {
        continue;
      }
      $ud[$v] = file_get_contents($udir.'/'.$v);
    }
    file_put_contents($udir.'/index.json', json_encode($ud));    
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
    // do not just check uid existance
    // also check user name 
    // or it will return bad things for setUserData function  
    return file_exists(self::getUserDir($uid).'/name');
  }

  static public function getUserChannelsDir($uid) {
    $udir = self::getUsersDir();
    return $udir.'/'.$uid.'/channels';
  }

  static public function getUserChannels($uid, $injson = false) {
    $ucdir = self::getUserChannelsDir($uid);
    $channels = array();
    foreach(scandir($ucdir) as $value) {
      if($value === '.' || $value === '..') {continue;}
      $channels[] = $value;
    }
    return $injson ? json_encode($channels) : $channels;
  }

  static public function joinChannel($uid, $cid) {
    $cupath = Container_channels::getChannelUserPath($cid, $uid);    
    $ucpath  = self::getUserChannelsDir($uid).'/'.$cid;
    if (file_exists($ucpath) and file_exists($cupath)) {
      return false;
    } else {
      touch($ucpath);
      touch($cupath);
    }
  }

  static public function leaveChannel($uid, $cid) {
    $ucdir = self::getUserChannelsDir($uid);
    if (file_exists($ucdir.'/'.$cid)) {
      unlink($ucdir.'/'.$cid);
      return true;
    } else {
      return false;
    }
  }
}
