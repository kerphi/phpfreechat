<?php

include_once 'container/channels.php';

class Container_channels_op {
  
  /**
   * Returns the $cid channel operators list
   */
  static public function getOpList($cid) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/op/*';
    return array_map('basename', glob($p));
  }
  
  /**
   * Sets $uid as an operator on $cid
   */
  static public function addOp($cid, $uid) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/op/'.$uid;  
    @touch($p);
    return file_exists($p);
  }

  /**
   * Remove $uid operator on $cid
   */
  static public function rmOp($cid, $uid) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/op/'.$uid;  
    @unlink($p);
    return !file_exists($p);
  }
  
  /**
   * Check if $uid is an operator on $cid
   */
  static public function isOp($cid, $uid) {
    $cdir = Container_channels::getChannelsDir();
    $p    = $cdir.'/'.$cid.'/op/'.$uid;  
    return file_exists($p);
  }
}
