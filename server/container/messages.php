<?php

include_once 'container/channels.php';
include_once 'container/users.php';

class Container_messages {
  
  /**
   * cid : recipient
   * uid : sender
   * msg : message to send
   */
  static public function postMsgToChannel($cid, $uid, $body, $type = 'msg') {

    $mid = self::generateMid($cid);
    $msg = json_encode(array(
      'id'        => $mid,
      'sender'    => $uid,
      'recipient' => 'channel|'.$cid,
      'type'      => $type,
      'body'      => $body,
      'timestamp' => time(),
    ));

    // search users subscribed to the channel
    foreach(Container_channels::getChannelUsers($cid) as $subuid) {
      // post this message on each users subscribed on the channel
      // /users/:uid/msg/
      if ($subuid != $uid) { // don't post message to the current connected user
        $umdir = Container_users::getUserMsgDir($subuid);
        file_put_contents($umdir.'/'.$mid, $msg);
      }
    }
    
    return $msg;
  }
  
  /**
   * Generates a unique message id
   */
  static public function generateMid($cid) {
    $mid = sha1(uniqid('', true));
    return $mid;
  }

}

