<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_update extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param = "")
  {
    $c =& $this->c;
    // do not update if user isn't active (have quit)
    if ($c->active)
    {
      $cmd =& pfcCommand::Factory("updatemynick", $c);
      $cmd->run($xml_reponse, $clientid);
      $cmd =& pfcCommand::Factory("getonlinenick", $c);
      $cmd->run($xml_reponse, $clientid);
      $cmd =& pfcCommand::Factory("getnewmsg", $c);
      $cmd->run($xml_reponse, $clientid);
    }
  }
}

?>