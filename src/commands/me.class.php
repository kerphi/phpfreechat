<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_me extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    $container =& $c->getContainerInstance();
    $msg = phpFreeChat::PreFilterMsg($param);
    $container->write($recipient, "*me*", $this->name, $u->nick." ".$msg);
    
    if ($c->debug) pxlog("/me ".$msg, "chat", $c->getId());
  }
}

?>