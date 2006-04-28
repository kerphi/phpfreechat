<?php
/**
 * phpfreechat.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_getnewmsg extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;

    // check this methode is not being called
    if( isset($_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid]) )
    {
      // kill the lock if it has been created more than 10 seconds ago
      $last_10sec = time()-10;
      $last_lock = $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid];
      if ($last_lock < $last_10sec) $_SESSION[$c->prefix."lock_".$c->getId()."_".$clientid] = 0;
      if ( $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] != 0 ) exit;
    }

    // create a new lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] = time();

    $container =& $c->getContainerInstance();
    
    $from_id = isset($_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid]) ? $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] : $container->getLastMsgId()-$c->max_msg;
    $nbnewmsg = isset($_SESSION[$c->prefix."nbreadmsg_".$c->getId()."_".$clientid]) ? $_SESSION[$c->prefix."nbreadmsg_".$c->getId()."_".$clientid] : 0;
    
    $new_msg = $container->readNewMsg($from_id);
    $new_from_id = $new_msg["new_from_id"];
    $messages    = $new_msg["messages"];

    // transform new message in html format
    $js = '';
    $msg_sent = false;
    foreach ($messages as $msg)
    {
      $m_id     = isset($msg[0]) ? $msg[0] : "";
      $m_date   = isset($msg[1]) ? $msg[1] : "";
      $m_heure  = isset($msg[2]) ? $msg[2] : "";
      $m_nick   = isset($msg[3]) ? $msg[3] : "";
      $m_words  = phpFreeChat::PostFilterMsg(isset($msg[4]) ? $msg[4] : "");
      $m_cmd    = "cmd_msg";
      if (preg_match("/\*([a-z]*)\*/i", $msg[3], $res))
      {
	if ($res[1] == "notice")
	  $m_cmd = "cmd_notice";
	else if ($res[1] == "me")
	  $m_cmd = "cmd_me";
      }

      $js .= "Array(".$m_id.",'".addslashes($m_date)."','".addslashes($m_heure)."','".addslashes($m_nick)."','".addslashes($m_words)."','".addslashes($m_cmd)."',".(date("d/m/Y") == $m_date ? 1 : 0).",".($nbnewmsg == 0 ? 1 : 0)."),";
      $msg_sent = true;
    }
    if ($js != "")
    {
      $js = substr($js, 0, strlen($js)-1); // remove last ','
      $js = 'Array('.$js.')';
      $xml_reponse->addScript("pfc.parseAndPost(".$js.");");
    }

    if ($msg_sent)
    {
      // store the new msg id
      $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] = $new_from_id;
      $_SESSION[$c->prefix."nbreadmsg_".$c->getId()."_".$clientid] = $nbnewmsg + count($messages);
    }

    // remove the lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] = 0;
  }
}

?>
