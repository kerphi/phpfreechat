<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_kick extends pfcCommand
{
  var $usage = "/kick {nickname} [ {reason} ]";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& $this->c;
    $u =& $this->u;

    if (trim($params[0]) == '')
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }
    
    // kicking a user just add a command to play to the aimed user metadata.
    $ct =& $c->getContainerInstance();
    $otherid  = $ct->getNickId($params[0]);
    $channame = $u->channels[$recipientid]["name"];
    $cmdstr = 'leave';
    $cmdp = array();
    $cmdp['params'][] = $channame; // channel name
    $cmdp['params'][] = _pfc("kicked from %s by %s - reason: %s", $channame, $sender, $params[1]); // reason
    pfcCommand::AppendCmdToPlay($otherid, $cmdstr, $cmdp);
  }
}

?>