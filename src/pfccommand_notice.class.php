<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_notice extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg = "", $level = 2)
  {
    $c =& $this->c;
    if ($c->shownotice > 0 &&
        $c->shownotice >= $level)
    {
      $container =& $c->getContainerInstance();
      $msg = phpFreeChat::FilterSpecialChar($msg);
      $container->writeMsg("*notice*", $msg);
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=true msg=".$msg, "chat", $c->getId());
    }
    else
    {
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=false", "chat", $c->getId());
    }
  }
}

?>