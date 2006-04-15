<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_connect extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;


    // disconnect last connected users from the server if necessary 
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoleteNick(NULL, $c->timeout);

    /*
    // reset the nickname cache
    $_SESSION[$c->prefix."nicklist_".$c->getId()."_".$clientid] = NULL;
    
    // disable or not the nickname button if the frozen_nick is on/off
    if ($c->frozen_nick)
      $xml_reponse->addAssign($c->prefix."handle", "disabled", true);
    else
      $xml_reponse->addAssign($c->prefix."handle", "disabled", false);

    // disconnect last connected users if necessary 
    $cmd =& pfcCommand::Factory("getonlinenick");
    $cmd->run($xml_reponse, $clientid);
    
    // check if the wanted nickname was allready known
    if ($c->debug)
    {
      $container =& $c->getContainerInstance();
      $nickid    = $container->getNickId($u->nick);
      pxlog("/connect (nick=".$u->nick." nickid=".$nickid.")", "chat", $c->getId());
    }

    if ($u->nick == "")
    {
      // ask user to choose a nickname
      $cmd =& pfcCommand::Factory("asknick");
      $cmd->run($xml_reponse, $clientid, "");
    }
    else
    {
      $cmd =& pfcCommand::Factory("nick");
      $cmd->run($xml_reponse, $clientid, $u->nick);
    }
    */

    // start updates
    //    $xml_reponse->addScript("pfc.updateChat(true);");
    //    $xml_reponse->addScript("pfc.isconnected = true; pfc.refresh_loginlogout();");

    // connect to the server
    $xml_reponse->addScript("pfc.handleResponse('connect', 'ok', '');");
        
    return $clientid;
  }
}

?>