<?php

include_once 'container/channels.php';

class Container_channels_ban {
  
  /**
   * Returns the $cid channel banished names list
   */
  static public function getBanList($cid) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/ban/*';
    
    $result = array();
    foreach(glob($p) as $f) {
      $baninfo = json_decode(file_get_contents($f), true);
      $f = basename($f);
      $f = base64_decode($f);
      $result[$f] = $baninfo;
    }
    
    return $result;
  }
  
  /**
   * Adds $name64 (base64 encoded username) in the ban list of $cid
   */
  static public function addBan($cid, $name64, $baninfo) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/ban/'.$name64;
    @file_put_contents($p, json_encode($baninfo));
    return file_exists($p);
  }

  /**
   * Remove $name64 (base64 encoded username) from the ban list of $cid
   */
  static public function rmBan($cid, $name64) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/ban/'.$name64;  
    @unlink($p);
    return !file_exists($p);
  }
  
  /**
   * Check if $name is banished from $cid
   */
  static public function isBan($cid, $name) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/ban/'.base64_encode($name);  
    return file_exists($p);
  }
  
  /**
   * Returns the ban reason
   */
  static public function getBanInfo($cid, $name) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/ban/'.base64_encode($name);  
    return file_exists($p) ? json_decode(file_get_contents($p), true) : '{}';
  }
  
}
