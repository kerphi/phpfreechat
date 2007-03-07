<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_op extends pfcCommand
{
  var $usage = "/op {nickname}";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    if (trim($param) == "")
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }

    // just change the "isadmin" meta flag
    $nicktoop   = trim($param);
    $nicktoopid = $ct->getNickId($nicktoop);
    $ct->setUserMeta($nicktoopid, 'isadmin', true);

    $this->forceWhoisReload($nicktoopid);
  }
}

?>