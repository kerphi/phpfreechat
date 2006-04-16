<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_quit extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    // set the chat inactive
    $u->active = false;
    $u->saveInCache();

    // then remove the nickname file
    $container =& $c->getContainerInstance();
    foreach( $u->channels as $id => $chan )
      if ($container->removeNick($chan, $u->nick))
      {
        $cmd =& pfcCommand::Factory("notice");
        $cmd->run($xml_reponse, $clientid, _pfc("%s quit", $u->nick), $sender, $chan["recipient"], $id, 2);
      }
    foreach( $u->privmsg as $id => $pv )
      if ($container->removeNick($pv, $u->nick))
      {
        $cmd =& pfcCommand::Factory("notice");
        $cmd->run($xml_reponse, $clientid, _pfc("%s quit", $u->nick), $sender, $pv["recipient"], $id, 2);
      }


    $xml_reponse->addScript("pfc.handleResponse('quit', 'ok', '');");

    if ($c->debug) pxlog("/quit (a user just quit -> nick=".$u->nick.")", "chat", $c->getId());
  }
}

?>