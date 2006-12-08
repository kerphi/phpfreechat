<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_ban extends pfcCommand
{
  var $usage = "/ban {nickname} [ {reason} ]";
  
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

    $ct =& $c->getContainerInstance();
    $nickidtoban = $ct->getNickId($params[0]);
    
    // notify all the channel
    $cmdp = $p;
    $cmdp["param"] = _pfc("banished from %s by %s", $recipient, $sender);
    $cmdp["flag"]  = 1;
    $cmd =& pfcCommand::Factory("notice");
    $cmd->run($xml_reponse, $cmdp);
    
    // kick the user (maybe in the future, it will be dissociate in a /kickban command)
    $cmdp = $p;
    $cmdp["params"]   = array();
    $cmdp["params"][] = $params[0]; // nickname to kick
    $cmdp["params"][] = $params[1]; // reason
    $cmd =& pfcCommand::Factory("kick");
    $cmd->run($xml_reponse, $cmdp);


    // update the recipient banlist
    $banlist = $ct->getChanMeta($recipient, 'banlist_nickid');
    if ($banlist == NULL)
      $banlist = array();
    else
      $banlist = unserialize($banlist);
    $banlist[] = $nickidtoban; // append the nickid to the banlist
    $ct->setChanMeta($recipient, 'banlist_nickid', serialize($banlist));
  }
}

?>