<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_deop extends pfcCommand
{
  var $usage = "/deop {nickname}";
  
  function run(&$xml_reponse, $p)
  {
    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    if (trim($p["param"]) == "")
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
    $nicktodeop   = trim($p["param"]);
    $nicktodeopid = $ct->getNickId($nicktodeop);
    $ct->setUserMeta($nicktodeopid, 'isadmin', false);

    $this->forceWhoisReload($nicktodeopid);
  }
}

?>