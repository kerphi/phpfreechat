<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_quit extends pfcCommand
{
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

    $nick = $ct->getNickname($u->nickid);
    
    // leave the channels
    foreach( $u->channels as $id => $chandetail )
      if ($ct->removeNick($chandetail["recipient"], $u->nickid))
      {
        $cmdp = $p;
        $cmdp["param"] = $id;
        $cmdp["recipient"] = $chandetail["recipient"];
        $cmdp["recipientid"] = $id;
        $cmd =& pfcCommand::Factory("leave");
        $cmd->run($xml_reponse, $cmdp);
      }
    // leave the private messages
    foreach( $u->privmsg as $id => $pvdetail )
      if ($ct->removeNick($pvdetail["recipient"], $u->nickid))
      {
        $cmdp = $p;
        $cmdp["param"] = $id;
        $cmdp["recipient"] = $pvdetail["recipient"];
        $cmdp["recipientid"] = $id;        
        $cmd =& pfcCommand::Factory("leave");
        $cmd->run($xml_reponse, $cmdp);
      }
    // leave the server
    $ct->removeNick(NULL, $u->nickid);

    /*
    // then set the chat inactive
    $u->active = false;
    $u->saveInCache();
    */
    
    $xml_reponse->script("pfc.handleResponse('quit', 'ok', '');");
  }
}

?>
