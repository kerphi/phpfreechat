<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_notice extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg, $sender, $recipient, $recipientid, $flags = 3)
  {
    $c =& $this->c;
    $u =& $this->u;
    
    if ($c->shownotice > 0 &&
        ($c->shownotice & $flags) == $flags)
    {
      $container =& $c->getContainerInstance();
      $msg = phpFreeChat::FilterSpecialChar($msg);
      $container->write($recipient, $u->nick, "notice", $msg);
    }
    if ($c->debug) pxlog("/notice ".$msg." (flags=".$flags.")", "chat", $c->getId());
  }
}

?>
