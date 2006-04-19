<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_connect extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param = "")
  {
    $c =& $this->c;

    // reset the message id indicator
    // i.e. be ready to re-get all last posted messages
    $container =& $c->getContainerInstance();
    $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] = $container->getLastMsgId()-$c->max_msg;
    // initialize the number of new read messages in order to be able to know how old a message is
    $_SESSION[$c->prefix."nbreadmsg_".$c->getId()."_".$clientid] = 0;

    // reset the nickname cache
    $_SESSION[$c->prefix."nicklist_".$c->getId()."_".$clientid] = NULL;
    
    // disable or not the nickname button if the frozen_nick is on/off
    if ($c->frozen_nick)
      $xml_reponse->addAssign($c->prefix."handle", "disabled", true);
    else
      $xml_reponse->addAssign($c->prefix."handle", "disabled", false);

    // disconnect last connected users if necessary 
    $cmd =& pfcCommand::Factory("getonlinenick", $c);
    $cmd->run($xml_reponse, $clientid);
    
    // check if the wanted nickname was allready known
    if ($c->debug)
    {
      $container =& $c->getContainerInstance();
      $nickid    = $container->getNickId($c->nick);
      pxlog("Cmd_connect[".$c->sessionid."]: nick=".$c->nick." nickid=".$nickid, "chat", $c->getId());
    }

    if ($c->nick == "")
    {
      // ask user to choose a nickname
      $cmd =& pfcCommand::Factory("asknick", $c);
      $cmd->run($xml_reponse, $clientid, "");
    }
    else
    {
      $cmd =& pfcCommand::Factory("nick", $c);
      $cmd->run($xml_reponse, $clientid, $c->nick);
    }
    
    // start updates
    $xml_reponse->addScript("pfc.updateChat(true);");
    $xml_reponse->addScript("pfc.isconnected = true; pfc.refresh_loginlogout();");

    // give focus the the input text box if wanted
    if($c->focus_on_connect)
      $xml_reponse->addScript("$('".$c->prefix."words').focus();");
    
    return $clientid;
  }
}

?>
