<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_me extends pfcCommand
{
  var $usage = "/me {message}";

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

    $msg = phpFreeChat::PreFilterMsg($param);
    $ct->write($recipient, "*me*", $this->name, $u->getNickname()." ".$msg);
  }
}

?>
