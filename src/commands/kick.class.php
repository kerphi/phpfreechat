<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_kick extends pfcCommand
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
      if (!isset($cmdtoplay["quit"])) $cmdtoplay["quit"] = array();
      $cmdtoplay["quit"][] = "dummy param";
      $container->setMeta(serialize($cmdtoplay), "cmdtoplay", "nickname", $nickid);      
    }
    $xml_reponse->addScript("alert('/kick $param command -> $nickid');");

    
    /*
    $cmdtoplay = $container->getMeta("cmdtoplay", "nickname", $u->privmsg[$recipientid]["pvnickid"]);
    if (is_string($cmdtoplay)) $cmdtoplay = unserialize($cmdtoplay);
    if (!is_array($cmdtoplay)) $cmdtoplay = array();
    if (!isset($cmdtoplay["privmsg2"])) $cmdtoplay["privmsg2"] = array();
    if (!in_array($u->nick, $cmdtoplay["privmsg2"]))
    {
      $cmdtoplay["privmsg2"][] = $u->nick;
      $container->setMeta(serialize($cmdtoplay), "cmdtoplay", "nickname", $u->privmsg[$recipientid]["pvnickid"]);
      //          $xml_reponse->addScript("alert('cmdtoplay[]=".serialize($cmdtoplay)."');");
    }
    */

    
    
  }
}

?>