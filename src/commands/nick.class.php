<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_nick extends pfcCommand
{
  var $usage = "/nick {newnickname}";
  
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

    if (trim($param) == '')
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return false;
    }
    
    $newnick = phpFreeChat::FilterNickname($param);
    $oldnick = $ct->getNickname($u->nickid);

    $newnickid = $ct->getNickId($newnick);
    $oldnickid = $u->nickid;

    // new nickname is undefined (not used) and
    // current nickname (oldnick) is mine and
    // oldnick is different from new nick
    // -> this is a nickname change
    if ($oldnick != $newnick &&
        $oldnick != '')
    {
      // really change the nick (rename it)
      $ct->changeNick($newnick, $oldnick);
      $u->nick = $newnick;
      $u->saveInCache();
      $this->forceWhoisReload($u->nickid);

      // notify all the joined channels/privmsg
      $cmdp = $p;
      $cmdp["param"] = _pfc("%s changes his nickname to %s",$oldnick,$newnick);
      $cmdp["flag"]  = 1;
      $cmd =& pfcCommand::Factory("notice");
      foreach($u->channels as $id => $chan)
      {
        $cmdp["recipient"]   = $chan["recipient"];
        $cmdp["recipientid"] = $id;
        $cmd->run($xml_reponse, $cmdp);
      }
      foreach( $u->privmsg as $id => $pv )
      {
        $cmdp["recipient"]   = $pv["recipient"];
        $cmdp["recipientid"] = $id;
        $cmd->run($xml_reponse, $cmdp);
      }
      $xml_reponse->script("pfc.handleResponse('nick', 'changed', '".addslashes($newnick)."');");
      return true;
    }

    // new nickname is undefined (not used)
    // -> this is a first connection (this piece of code is called by /connect command)
    if ($newnickid == '')
    {
      // this is a first connection : create the nickname on the server
      $ct->createNick($u->nickid, $newnick);
      $u->nick = $newnick;
      $u->saveInCache();
      
      $this->forceWhoisReload($u->nickid);

      $xml_reponse->script("pfc.handleResponse('nick', 'connected', '".addslashes($newnick)."');");
    
      return true;
    }

    return false;
  }
}

?>
