<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_unban extends pfcCommand
{
  var $usage = "/unban {nickname}";
  
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

    $ct =& pfcContainer::Instance();

    $nick = isset($params[0]) ? $params[0] : '';
    $nickid = $ct->getNickId($nick);
      
    if ($nick == "")
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }

    
    $updated = false;
    $msg = "<p>"._pfc("Nobody has been unbanished")."</p>";
    
    // update the recipient banlist
    $banlist = $ct->getChanMeta($recipient, 'banlist_nickid');
    if ($banlist == NULL)
      $banlist = array();
    else
      $banlist = unserialize($banlist);
    $nb = count($banlist);

    if (in_array($nickid, $banlist))
    {
      $banlist = array_diff($banlist, array($nickid));
      $ct->setChanMeta($recipient, 'banlist_nickid', serialize($banlist));
      $updated = true;
      $msg = "<p>"._pfc("%s has been unbanished", $nick)."</p>";
    }
    else if ($nick == "all") // @todo move the "/unban all" command in another command /unbanall
    {
      $banlist = array();
      $ct->setChanMeta($recipient, 'banlist_nickid', serialize($banlist));
      $updated = true;
      $msg = "<p>"._pfc("%s users have been unbanished", $nb)."</p>";
    }
    
    if ($updated)
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', '".$msg."');");
    else
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ko', '".$msg."');");
  }
}

?>