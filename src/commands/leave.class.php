<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");
require_once(dirname(__FILE__)."/../commands/join.class.php");

class pfcCommand_leave extends pfcCommand
{
  var $usage = "/leave [ch|pv [[{channel|nickname}] {reason}]]";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    $type   = isset($params[0]) ? $params[0] : '';
    $name   = isset($params[1]) ? $params[1] : '';
    $reason = isset($params[2]) ? $params[2] : '';

    if ($type != 'ch' && $type != 'pv' &&  $type != '')
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }

    
    // get the recipientid to close (a pv or a channel)
    $id = '';
    if ($type == 'ch')
    {
      if ($name == '')
        $id = $recipientid;
      else
        $id = pfcCommand_join::GetRecipientId($name);
    }
    else if ($type == 'pv')
    {
      // pv
      $pvnickid = $ct->getNickId($name);
      $nickid   = $u->nickid;
      if ($pvnickid != '')
      {
        // generate a pvid from the two nicknames ids
        $a = array($pvnickid, $nickid); sort($a);
        $pvrecipient = "pv_".$a[0]."_".$a[1];
        $id = md5($pvrecipient);
      }
    }
    else
      $id = $recipientid;
    

    
    $leavech = false;
    $leavepv = false;
    $leave_recip = '';
    $leave_id    = '';

    // save the new channel list in the session
    if ( isset($u->channels[$id]) )
    {
      $leave_recip = $u->channels[$id]["recipient"];
      $leave_id    = $id;
      unset($u->channels[$id]);
      $u->saveInCache();
      $leavech = true;
    }

    // save the new private messages list in the session
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
      //      if ($leavech)
      {
        // show a leave message with the showing the reason if present
        $cmdp = $p;
        $cmdp["recipient"]   = $leave_recip;
        $cmdp["recipientid"] = $leave_id;
        $cmdp["flag"]        = 2;
        $cmdp["param"] = _pfc("%s quit",$u->getNickname());
        if ($reason != "") $cmdp["param"] .= " (".$reason.")";
        $cmd =& pfcCommand::Factory("notice");
        $cmd->run($xml_reponse, $cmdp);
      }

      // remove the nickname from the channel/pv
      $ct->removeNick($leave_recip, $u->nickid);

      // reset the sessions indicators
      $chanrecip = $leave_recip;
      $chanid    = $leave_id;
      // reset the fromid flag
      $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$chanid;
      $from_id     = $ct->getLastId($chanrecip)-$c->max_msg;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
      // reset the oldmsg flag
      $oldmsg_sid = "pfc_oldmsg_".$c->getId()."_".$clientid."_".$chanid;
      $_SESSION[$oldmsg_sid] = true;

      // if the /leave command comes from a cmdtoplay then show the reason to the user (ex: kick or ban reason)
      if ($p['cmdtoplay'])
      {
        $cmdp = $p;
        $cmdp["param"] = $reason;
        $cmd =& pfcCommand::Factory("error");
        $cmd->run($xml_reponse, $cmdp);
      }
      
      // return ok to the client
      // then the client will remove the channel' tab
      $xml_reponse->script("pfc.handleResponse('leave', 'ok', '".$id."');");
    }
    else
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
    }
  }
}

?>