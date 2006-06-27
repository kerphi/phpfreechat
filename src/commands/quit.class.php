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
    $quitmsg = $param == "" ? _pfc("%s quit", $u->nick) : _pfc("%s quit (%s)", $u->nick, $param);
    
    // from the channels
    foreach( $u->channels as $id => $chandetail )
      if ($container->removeNick($chandetail["recipient"], $u->nick))
      {
        $cmd =& pfcCommand::Factory("leave");
        $cmd->run($xml_reponse, $clientid, $id, $sender, $chandetail["recipient"], $id, 2);
      }
    // from the private messages
    foreach( $u->privmsg as $id => $pvdetail )
      if ($container->removeNick($pvdetail["recipient"], $u->nick))
      {
        $cmd =& pfcCommand::Factory("leave");
        $cmd->run($xml_reponse, $clientid, $id, $sender, $pvdetail["recipient"], $id, 2);
      }
    // from the server
    $container->removeNick(NULL, $u->nick);


    $xml_reponse->addScript("pfc.handleResponse('quit', 'ok', '');");

    if ($c->debug) pxlog("/quit (a user just quit -> nick=".$u->nick.")", "chat", $c->getId());
  }
}

?>