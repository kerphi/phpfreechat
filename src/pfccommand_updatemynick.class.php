<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_updatemynick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;
    $container =& $c->getContainerInstance();
    $was_there = $container->updateNick($c->nick);
    if (!$was_there)
    {
      /*
      @todo: write the timeout adjustment when the user object will be available
      if ($c->debug) pxlog("Cmd_updateMyNick[".$c->sessionid."]: nick ".$c->nick." updated but was not there, adjust timeout to ".$c->timeout, "chat", $c->getId());
      // adjust the timeout value dynamicaly for this user
      $c->timeout += $c->refresh_delay;
      $c->saveInSession();
      */
    }
  }
}

?>