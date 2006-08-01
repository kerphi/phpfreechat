<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_me extends pfcCommand
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
    $msg = phpFreeChat::PreFilterMsg($param);
    $container->write($recipient, "*me*", $this->name, $u->nick." ".$msg);
    
    if ($c->debug) pxlog("/me ".$msg, "chat", $c->getId());
  }
}

?>