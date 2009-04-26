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
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    $nick   = isset($params[0]) ? $params[0] : '';
    $reason = isset($params[1]) ? $params[1] : '';
    if ($reason == '') $reason = _pfc("no reason");

    // to allow unquotted reason
    if (count($params) > 2) 
      for ($x=2;$x<count($params);$x++) 
        $reason.=" ".$params[$x];
    
    $channame = $u->channels[$recipientid]["name"];
    
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

    $ct =& pfcContainer::Instance();
    $nickidtoban = $ct->getNickId($nick);
    
    // notify all the channel
    $cmdp = $p;
    $cmdp["param"] = _pfc("%s banished from %s by %s", $nick, $channame, $sender);
    $cmdp["flag"]  = 4;
    $cmd =& pfcCommand::Factory("notice");
    $cmd->run($xml_reponse, $cmdp);
    
    // kick the user (maybe in the future, it will exists a /kickban command)
    $cmdp = $p;
    $cmdp["params"]   = array();
    $cmdp["params"][] = $nick; // nickname to kick
    $cmdp["params"][] = $reason; // reason
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