<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_init extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;
    
    $cmd =& pfcCommand::Factory("quit");
    $cmd->run($xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);

    $u->destroy();
  }
}

?>