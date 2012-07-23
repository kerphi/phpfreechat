<?php

class Messages_utils {
  
  /**
   * cid : recipient
   * uid : sender
   * msg : message to send
   */
  static public function postMsgToChannel($cid, $uid, $msg) {
    include_once 'routes/channels.php';
    include_once 'routes/users.php';

    $mid = self::generateMid($cid);
    $msg = json_encode(array(
      'id'        => $mid,
      'sender'    => $uid,
      'recipient' => 'channel|'.$cid,
      'message'   => $msg,
      'timestamp' => time(),
    ));

    // search users subscribed to the channel
    include_once 'routes/users.php';
    foreach(Channels_utils::getChannelUsers($cid) as $subuid) {
      // post this message on each users subscribed on the channel
      // /users/:uid/msg/
      if ($subuid != $uid) { // don't post message to the current connected user
        $umdir = Users_utils::getUserMsgDir($subuid);
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

