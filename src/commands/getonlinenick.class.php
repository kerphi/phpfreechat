<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_getonlinenick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $container =& $c->getContainerInstance();

    // take care to disconnect timeouted users on this channel
    $disconnected_users = $container->removeObsoleteNick($recipient,$c->timeout);
    foreach ($disconnected_users["nick"] as $n)
    {
      $cmd =& pfcCommand::Factory("notice");
      $cmd->run($xml_reponse, $clientid, _pfc("%s quit (timeout)", $n), $sender, $recipient, $recipientid, 2);
    }
    
    // get the cached nickname list
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid."_".$recipientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();

    // get the real nickname list
    $users = $container->getOnlineNick($recipient);
    if ($oldnicklist != $users["nickid"]) // check if the nickname list must be updated on the client side
    {
      $_SESSION[$nicklist_sid] = $users["nickid"];

      // sort the nicknames
      $nicklist = array();
      foreach($users["nickid"] as $nid)
        $nicklist[] = $container->getNickname($nid);
      sort($nicklist);
      
      if ($c->debug)
      {
        $nicklist = implode(",",$nicklist);
        pxlog("/getonlinenick (nicklist updated - nicklist=".$nicklist.")", "chat", $c->getId());
      }

      // build and send the nickname list
      $js = "";
      foreach ($nicklist as $nick)
      {
        $nickname = addslashes($nick); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js = substr($js, 0, strlen($js)-1); // remove last ','
      $xml_reponse->addScript("pfc.updateNickList('".$recipientid."',Array(".$js."));");
    }
  
  }
}

?>