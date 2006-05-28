<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_unban extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    $container =& $c->getContainerInstance();


    $updated = false;
    $msg = "<p>"._pfc("Nobody has been unbanished")."</p>";
    
    // update the recipient banlist
    $banlist = $container->getMeta("banlist_nickid", "channel", $recipientid);
    if ($banlist == NULL)
      $banlist = array();
    else
      $banlist = unserialize($banlist);
    $nb = count($banlist);

    if (in_array($param, $banlist))
    {
      $banlist = array_diff($banlist, array($param));
      $container->setMeta(serialize($banlist), "banlist_nickid", "channel", $recipientid);
      $updated = true;
      $msg = "<p>"._pfc("%s has been unbanished", $param)."</p>";
    }
    else if ($param == "all")
    {
      $banlist = array();
      $container->setMeta(serialize($banlist), "banlist_nickid", "channel", $recipientid);
      $updated = true;
      $msg = "<p>"._pfc("%s users have been unbanished", $nb)."</p>";
    }
    
    if ($updated)
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ok', '".$msg."');");
    else
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ko', '".$msg."');");
  }
}

?>