<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_updatemynick extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& $this->c;
    $u =& $this->u;

    $container =& $c->getContainerInstance();
    $was_there = $container->updateNick($recipient, $u->nick);
    if (!$was_there)
    {
      // if the user were not in the list, it must be created in order to refresh his metadata
      // because when the user is timeouted, his metadata are destroyed.
      $container->createNick($recipient, $u->nick, $u->nickid);
      
      /*
      @todo: write the timeout adjustment when the user object will be available
      if ($c->debug) pxlog("Cmd_updateMyNick[".$c->sessionid."]: nick ".$u->nick." updated but was not there, adjust timeout to ".$c->timeout, "chat", $c->getId());
      // adjust the timeout value dynamicaly for this user
      $c->timeout += $c->refresh_delay;
      $c->saveInCache();
      */
    }
  }
}

?>