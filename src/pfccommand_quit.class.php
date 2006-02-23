<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_quit extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;
   
    // set the chat inactive
    $c->active = false;
    $c->saveInSession();

    // then remove the nickname file
    $container =& $c->getContainerInstance();
    if ($container->removeNick($c->nick))
    {
      $cmd =& pfcCommand::Factory("notice", $c);
      $cmd->run($xml_reponse, $clientid, _pfc("%s quit", $c->nick), 2);
    }

    // stop updates
    $xml_reponse->addScript("pfc.updateChat(false);");

    if ($c->debug) pxlog("Cmd_quit[".$c->sessionid."]: a user just quit -> nick=".$c->nick, "chat", $c->getId());
  }
}

?>