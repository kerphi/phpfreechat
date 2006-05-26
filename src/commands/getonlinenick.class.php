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
    foreach ($disconnected_users as $u)
    {
      $cmd =& pfcCommand::Factory("notice");
      $cmd->run($xml_reponse, $clientid, _pfc("%s quit (timeout)",$u["nick"]), $sender, $recipient, $recipientid, 2);
    }
    
    // get the cached nickname list
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid."_".$recipientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();

    // get the real nickname list
    $users = $container->getOnlineNick($recipient);
    sort($users);
    // check if the nickname list must be updated
    if ($oldnicklist != $users)
    {
      if ($c->debug)
      {
        $nicklist = array(); foreach($users as $u) $nicklist[] = $u["nick"]; $nicklist = implode(",",$nicklist);
        pxlog("/getonlinenick (nicklist updated - nicklist=".$nicklist.")", "chat", $c->getId());
      }

      $_SESSION[$nicklist_sid] = $users;

      $js = "";
      foreach ($users as $u)
      {
        $nickname = addslashes($u["nick"]); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js    = substr($js, 0, strlen($js)-1); // remove last ','
      
      $xml_reponse->addScript("pfc.updateNickList('".$recipientid."',Array(".$js."));");
    }
  
  }
}

?>