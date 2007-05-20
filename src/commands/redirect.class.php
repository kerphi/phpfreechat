<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_redirect extends pfcCommand
{
  var $usage = "/redirect url";

  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    if (trim($param) == '')
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }

    $xml_reponse->redirect($param);
  }
}

?>
