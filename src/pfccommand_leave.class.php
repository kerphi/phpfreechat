<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_leave extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    // tab to leave can be passed in the parameters
    $id = ($param != "") ? $param : $recipientid;
    
    //    $xml_reponse->addScript("alert('sender=".addslashes($sender)."');");
    //    $xml_reponse->addScript("alert('recipientid=".addslashes($id)."');");

    $leavech = false;
    $leavepv = false;
    $leave_recip = "";
    $leave_id    = "";

    // check into channels
    if ( isset($u->channels[$id]) )
    {
      $leave_recip = $u->channels[$id]["recipient"];
      $leave_id    = $id;
      unset($u->channels[$id]);
      $u->saveInCache();
      $leavech = true;
    }

    // check into private messages
    if ( isset($u->privmsg[$id]) )
    {
      $leave_recip = $u->privmsg[$id]["recipient"];
      $leave_id    = $id;
      unset($u->privmsg[$id]);
      $u->saveInCache();
      $leavepv = true;
    }

    if($leavepv || $leavech)
    {
      if ($leavech)
      {
        // show a leave message
        $cmd =& pfcCommand::Factory("notice");
        $cmd->run($xml_reponse, $clientid, _pfc("%s quit",$u->nick), $sender, $leave_recip, $leave_id, 1);
      }

      // remove the nickname from the channel/pv
      $container =& $c->getContainerInstance();
      $container->removeNick($leave_recip, $u->nick);
      
      // return ok to the client
      // then the client will remove the channel' tab
      $xml_reponse->addScript("pfc.handleResponse('leave', 'ok', '".$id."');");
    }
    else
    {
      // error
      $xml_reponse->addScript("alert('error leaving ".$id."');");
    }
  }
}

?>