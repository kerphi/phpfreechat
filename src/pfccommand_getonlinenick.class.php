<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_getonlinenick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;

    // get the actual nicklist
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();
    
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoleteNick();
    foreach ($disconnected_users as $u)
    {
      $cmd =& pfcCommand::Factory("notice", $c);
      $cmd->run($xml_reponse, $clientid, _pfc("%s disconnected (timeout)",$u), 2);
    }
    $users = $container->getOnlineNick();
    sort($users);
    // check if the nickname list must be updated
    if ($oldnicklist != $users)
    {
      if ($c->debug) pxlog("Cmd_getOnlineNick[".$c->sessionid."]: nicklist updated - nicklist=".var_export($users, true), "chat", $c->getId());

      $_SESSION[$nicklist_sid] = $users;

      $js = "";
      foreach ($users as $u)
      {
        $nickname = addslashes($u); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js    = substr($js, 0, strlen($js)-1); // remove last ','
      
      $xml_reponse->addScript("pfc.updateNickList(Array(".$js."));");
    }
  }
}

?>