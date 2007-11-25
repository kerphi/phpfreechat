<?php
/**
 * getnewmsg.class.php
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

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_getnewmsg extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& pfcGlobalConfig::Instance();
    // do nothing if the recipient is not defined
    if ($recipient == "") return;
       
    // check this methode is not being called
    if( isset($_SESSION["pfc_lock_readnewmsg_".$c->getId()."_".$clientid]) )
    {
      // kill the lock if it has been created more than 10 seconds ago
      $last_10sec = time()-10;
      $last_lock = $_SESSION["pfc_lock_readnewmsg_".$c->getId()."_".$clientid];
      if ($last_lock < $last_10sec) $_SESSION["pfc_lock_".$c->getId()."_".$clientid] = 0;
      if ( $_SESSION["pfc_lock_readnewmsg_".$c->getId()."_".$clientid] != 0 ) exit;
    }
    // create a new lock
    $_SESSION["pfc_lock_readnewmsg_".$c->getId()."_".$clientid] = time();

    // read the last from_id value
    $container =& pfcContainer::Instance();
    $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$recipientid;

    $from_id = 0;
    if (isset($_SESSION[$from_id_sid]))
      $from_id = $_SESSION[$from_id_sid];
    else
    {
      $from_id = $container->getLastId($recipient) - $c->max_msg - 1;
      if ($from_id < 0) $from_id = 0;
    }
    // check if this is the first time you get messages
    $oldmsg_sid = "pfc_oldmsg_".$c->getId()."_".$clientid."_".$recipientid;
    $oldmsg = false;
    if (isset($_SESSION[$oldmsg_sid]))
    {
      unset($_SESSION[$oldmsg_sid]);
      $oldmsg = true;
    }
    
    $new_msg     = $container->read($recipient, $from_id);
    $new_from_id = $new_msg["new_from_id"];
    $data        = $new_msg["data"];
    
    // transform new message in html format
    $js = array();
    $data_sent = false;
    foreach ($data as $d)
    {
      $m_id          = $d["id"];
      $m_date        = date($c->date_format, $d["timestamp"] + $c->time_offset);
      $m_time        = date($c->time_format, $d["timestamp"] + $c->time_offset);
      $m_sender      = $d["sender"];
      $m_recipientid = $recipientid;
      $m_cmd         = $d["cmd"];
      $m_param       = phpFreeChat::PostFilterMsg(pfc_make_hyperlink($d["param"]));
      $js[] = array($m_id,
                    $m_date,
                    $m_time,
                    $m_sender,
                    $m_recipientid,
                    $m_cmd,
                    $m_param,
                    date($c->date_format, time() + $c->time_offset) == $m_date ? 1 : 0, // posted today ?
                    $oldmsg ? 1 : 0); // is it a new message in the current session or is it a part of the history
      $data_sent = true;
    }
    if (count($js) != 0)
    {
      require_once dirname(__FILE__).'/../pfcjson.class.php';
      $json = new pfcJSON();      
      $js = $json->encode($js);
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', (".$js."));");
    }

    if ($data_sent)
    {
      // store the new msg id
      $_SESSION[$from_id_sid] = $new_from_id;
    }
    
    // remove the lock
    $_SESSION["pfc_lock_readnewmsg_".$c->getId()."_".$clientid] = 0;
    
  }
}

?>
