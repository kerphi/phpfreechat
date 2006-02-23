<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_me extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg)
  {
    $c =& $this->c;
    $container =& $c->getContainerInstance();
    $msg = phpFreeChat::PreFilterMsg($msg);
    $container->writeMsg("*me*", $c->nick." ".$msg);
    if ($c->debug) pxlog("Cmd_me[".$c->sessionid."]: msg=".$msg, "chat", $c->getId());
  }
}

?>