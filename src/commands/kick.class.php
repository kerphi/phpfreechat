<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_kick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;


    // kicking a user just add a command to play to the aimed user metadata.
    $container =& $c->getContainerInstance();
    $nickid = $container->getNickId($param);
    if ($nickid != "undefined")
    {
      $cmdtoplay = $container->getMeta("cmdtoplay", "nickname", $nickid);
      if (is_string($cmdtoplay)) $cmdtoplay = unserialize($cmdtoplay);
      if (!is_array($cmdtoplay)) $cmdtoplay = array();
      if (!isset($cmdtoplay["quit"])) $cmdtoplay["quit"] = array();
      $cmdtoplay["quit"][] = _pfc("kicked by %s", $sender);
      $container->setMeta(serialize($cmdtoplay), "cmdtoplay", "nickname", $nickid);      
    }
    
  }
}

?>