<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_me extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg)
  {
    $c =& $this->c;
    $u =& $this->u;

    $container =& $c->getContainerInstance();
    $msg = phpFreeChat::PreFilterMsg($msg);
    $container->writeMsg("*me*", $u->nick." ".$msg);
    if ($c->debug) pxlog("/me ".$msg, "chat", $c->getId());
  }
}

?>