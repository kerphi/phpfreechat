<?php

include_once 'container/channels.php';
include_once 'container/indexes.php';

class Container_users {
  
  static public function getDir() {
    $datadir = dirname(__FILE__).'/../data';
    $udir = $datadir.'/users';
    return $udir;
  }

  static public function generateUid($uid = null) {
    $udir = self::getDir();
    if (!$uid) {
      do {
        $uid = sha1(uniqid('', true));
        $upath = $udir.'/'.$uid;
      } while (file_exists($upath));
    } else {
      $upath = $udir.'/'.$uid;
    }
    @mkdir($upath, 0777, true);
    @mkdir($upath.'/messages', 0777, true);
    @mkdir($upath.'/channels', 0777, true);
    file_put_contents($upath.'/timestamp', time());
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
    $udir = self::getDir().'/'.$uid;
    if ($field) {
      $data = file_exists($udir.'/'.$field) ? file_get_contents($udir.'/'.$field) : '';
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
      if (self::checkUserExists($uid)) {
        Container_indexes::rmIndex('users/name', self::getUserData($uid, 'name'));
      }
      Container_indexes::setIndex('users/name', $userdata['name'], $uid);
    }
  
    // write user data on disk
    $ignore_keys = array('.', '..', 'index.json', 'messages', 'channels', 'id');
    $udir = self::getDir().'/'.$uid;
    foreach ($userdata as $k => $v) {
      if (in_array($k, $ignore_keys)) {
        continue;
      }
      file_put_contents($udir.'/'.$k, $v);
    }
    $ud = array();
    foreach (scandir($udir) as $v) {
      if (in_array($v, $ignore_keys)) {
        continue;
      }
      $ud[$v] = file_get_contents($udir.'/'.$v);
    }
    $ud['id'] = $uid;
    file_put_contents($udir.'/index.json', json_encode($ud));    
  }

  /**
   * Remove user's data
   */
  static public function rmUser($uid) {
    $udir = self::getDir().'/'.$uid;
    if (!is_dir($udir)) {
      return false;
    } else {
      // remove user's indexes
      Container_indexes::rmIndex('users/name', self::getUserData($uid, 'name'));
      // remove user's data
      rrmdir($udir);
      return true;
    }
  }

  /**
   * Update the close flag of a user
   * Called when the user close his window or reload his window
   */
  static public function setCloseFlag($uid) {
    $close_file = self::getDir().'/'.$uid.'/closed';
    if (!file_exists($close_file)) {
      touch($close_file);
      
      // adjust the timestamp so that when someone quit, the quit message occurs faster
      $ts_file = self::getDir().'/'.$uid.'/timestamp';
      $ts = (integer)file_get_contents($ts_file);
      $ts = $ts - $GLOBALS['pfc_timeout']/2;
      file_put_contents($ts_file, $ts);
    }
  }  
  
  /**
   * Update the last activity of a user
   * Has to be called each time the user check his messages
   */
  static public function setIsAlive($uid) {
    file_put_contents(self::getDir().'/'.$uid.'/timestamp', time());

    // remove if necessary the close flag (positioned when the user close his window)
    $close_file = self::getDir().'/'.$uid.'/closed';
    if (file_exists($close_file)) {
      unlink($close_file);
    }
  }
  
  /**
   * Run the garbage collector (disconnect timeouted users)
   * Has to be called by each users when they check pending messages
   */
  static public function runGC() {
    // get the GC time
    $gc = dirname(__FILE__).'/../data/gc';
    if (file_exists($gc)) {
      $gctime = (integer)file_get_contents($gc);
    } else {
      $gctime = time();
    }
    
    if (time() >= $gctime) {
      // write next gc check time
      file_put_contents($gc, time() + $GLOBALS['pfc_timeout']);

      // run the GC
      foreach (self::getUsers() as $uid) {
        $timestamp_file = self::getDir().'/'.$uid.'/timestamp';
        if (file_exists($timestamp_file)) {
          $timestamp = file_get_contents($timestamp_file);
          $timeouted = ($timestamp <= (time() - $GLOBALS['pfc_timeout']));
          if ($timeouted) {
            // check the leave reason
            $close_file = self::getDir().'/'.$uid.'/closed';
            $reason = file_exists($close_file) ? 'quit' : 'timeout';
            // disconnect the user (send leave messages on his channels)
            foreach (self::getUserChannels($uid) as $cid) {
              self::leaveChannel($uid, $cid);
              // post a leave message related to timeout
              $msg = Container_messages::postMsgToChannel($cid, $uid, $reason, 'leave');
            }
            // clear user data
            self::rmUser($uid);
          }
        } else {
          self::rmUser($uid); // no timeout file, just delete the user
        }
      }
    }
  }
  
  
  static public function getUsers() {
    $dir = self::getDir();
    $users = array();
    if (file_exists($dir)) {
      foreach (scandir($dir) as $value) {
          if ($value === '.' || $value === '..') continue;
          $users[] = $value;
      }
    }
    return $users;
  }
  
  static public function getUserMsgs($uid, $injson = false) {
    $umdir = self::getDir().'/'.$uid.'/messages';
    $msgs = array();
    foreach (scandir($umdir) as $value) {
      if ($value === '.' || $value === '..') continue;
      $msgs[] = file_get_contents($umdir.'/'.$value);
      unlink($umdir.'/'.$value);
    }
    return $injson ? '['.implode(',', $msgs).']' : array_map("json_decode", $msgs);
  }

  static public function checkUserExists($uid) {
    // do not just check uid existance
    // also check user's 'name' 
    // or it will return bad things for setUserData function  
    return file_exists(self::getDir().'/'.$uid.'/name');
  }

  static public function getUserChannels($uid, $injson = false) {
    $ucdir = self::getDir().'/'.$uid.'/channels';
    $channels = array();
    foreach (scandir($ucdir) as $value) {
      if ($value === '.' || $value === '..') continue;
      $channels[] = $value;
    }
    return $injson ? json_encode($channels) : $channels;
  }

  static public function joinChannel($uid, $cid) {
    Container_channels::init($cid);
    $cupath = Container_channels::getChannelUserPath($cid, $uid);    
    $ucpath  = self::getDir().'/'.$uid.'/channels/'.$cid;
    if (file_exists($ucpath) and file_exists($cupath)) {
      return false;
    } else {
      touch($ucpath);
      touch($cupath);
      return true;
    }
  }

  static public function leaveChannel($uid, $cid) {
    $ret = true;

    // clean user data
    $ucpath  = self::getDir().'/'.$uid.'/channels/'.$cid;
    if (file_exists($ucpath)) {
      unlink($ucpath);
    } else {
      $ret = false;
    }

    // clean channel data
    $cupath = Container_channels::getChannelUserPath($cid, $uid);    
    if (file_exists($cupath)) {
      unlink($cupath);
    } else {
      $ret = false;
    }

    // clean channel operator flag
    $p = $ucpath.'/op/'.$uid;
    if (file_exists($p)) {
      unlink($p);
    }

    return $ret;
  }
}

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}
