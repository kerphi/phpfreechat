<?php

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
    
    $c =& $this->c;
    // do nothing if the recipient is not defined
    if ($recipient == "") return;
    
    //$xml_reponse->addScript("alert('getnewmsg: sender=".addslashes($sender)." param=".addslashes($param)." recipient=".addslashes($recipient)." recipientid=".addslashes($recipientid)."');");
    
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
    $container =& $c->getContainerInstance();
    $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$recipientid;
    $from_id = 0;
    if (isset($_SESSION[$from_id_sid]))
      $from_id = $_SESSION[$from_id_sid];
    else
    {
      $from_id = $container->getLastId($recipient)-$c->max_msg;
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

    //$xml_reponse->addScript("alert('getnewmsg: fromidsid=".$from_id_sid."');");
    //$xml_reponse->addScript("alert('getnewmsg: recipient=".$recipient." fromid=".$from_id."');");

    //    $xml_reponse->addScript("alert('getnewmsg: recipientid=".$recipientid."');");
    
    $new_msg     = $container->read($recipient, $from_id);
    $new_from_id = $new_msg["new_from_id"];
    $data        = $new_msg["data"];
    
    //$xml_reponse->addScript("alert('getnewmsg: newmsg=".addslashes(var_export($data))."');");

    // transform new message in html format
    $js = '';
    $data_sent = false;
    foreach ($data as $d)
    {
      $m_id          = $d["id"];
      $m_date        = $d["date"];
      $m_time        = $d["time"];
      $m_sender      = $d["sender"];
      $m_recipientid = $recipientid;
      $m_cmd         = $d["cmd"];
      $m_param       = phpFreeChat::PostFilterMsg($d["param"]);
      $js .= "Array(".$m_id.",'".addslashes($m_date)."','".addslashes($m_time)."','".addslashes($m_sender)."','".addslashes($m_recipientid)."','".addslashes($m_cmd)."','".addslashes($m_param)."',".(date("d/m/Y") == $m_date ? 1 : 0).",".($oldmsg ? 1 : 0)."),";
      $data_sent = true;
    }
    if ($js != "")
    {
      $js = substr($js, 0, strlen($js)-1); // remove last ','
      $js = 'Array('.$js.')';
      $xml_reponse->addScript("pfc.handleComingRequest(".$js.");");
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