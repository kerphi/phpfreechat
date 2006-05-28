<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_ban extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;


    $container =& $c->getContainerInstance();
    $nickid = $container->getNickId($param);
    if ($nickid != "undefined")
    {
      $cmdtoplay = $container->getMeta("cmdtoplay", "nickname", $nickid);
      if (is_string($cmdtoplay)) $cmdtoplay = unserialize($cmdtoplay);
      if (!is_array($cmdtoplay)) $cmdtoplay = array();
      if (!isset($cmdtoplay["leave"])) $cmdtoplay["leave"] = array();
      $cmdtoplay["leave"][] = $recipientid; // ban the user from the current channel //_pfc("banished by %s", $sender);
      $container->setMeta(serialize($cmdtoplay), "cmdtoplay", "nickname", $nickid);      
    }

    // update the recipient banlist
    $banlist = $container->getMeta("banlist_nickid", "channel", $recipientid);
    if ($banlist == NULL)
      $banlist = array();
    else
      $banlist = unserialize($banlist);
    $banlist[] = $nickid; // append the nickid to the banlist
    $container->setMeta(serialize($banlist), "banlist_nickid", "channel", $recipientid);
  }
}

?>