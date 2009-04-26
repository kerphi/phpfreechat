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
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    $nick   = isset($params[0]) ? $params[0] : '';
    $reason = isset($params[1]) ? $params[1] : '';
    if ($reason == '') $reason = _pfc("no reason");

    // to allow unquotted reason
    if (count($params) > 2) 
      for ($x=2;$x<count($params);$x++) 
        $reason.=" ".$params[$x];
    
    if ($nick == '')
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
    $ct =& pfcContainer::Instance();
    $otherid  = $ct->getNickId($nick);
    $channame = $u->channels[$recipientid]["name"];
    $cmdstr = 'leave';
    $cmdp = array();
    $cmdp['flag'] = 4;
    $cmdp['params'][] = 'ch';
    $cmdp['params'][] = $channame; // channel name
    $cmdp['params'][] = _pfc("kicked from %s by %s - reason: %s", $channame, $sender, $reason); // reason
    pfcCommand::AppendCmdToPlay($otherid, $cmdstr, $cmdp);
  }
}

?>