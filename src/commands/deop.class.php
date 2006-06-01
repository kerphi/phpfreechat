<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_deop extends pfcCommand
{
  var $usage = "/deop {nickname}";
  
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    if (trim($param) == "")
    {
      // error
      $msg = _pfc("Missing parameter");
      $msg .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $clientid, $msg, $sender, $recipient, $recipientid);
      return;
    }

    // just change the "isadmin" meta flag
    $nicktodeop   = trim($param);
    $container  =& $c->getContainerInstance();
    $nicktodeopid = $container->getNickId($nicktodeop);
    $container->setMeta(false, "isadmin", "nickname", $nicktodeopid);
  }
}

?>