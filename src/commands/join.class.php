<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_join extends pfcCommand
{
  function run(&$xml_reponse, $clientid, &$param, &$sender, &$recipient, &$recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    $channame  = $param;
    $chanrecip = "ch_".$channame;
    $chanid    = md5($channame);
    
    if(!isset($u->channels[$chanid]))
    {
      $u->channels[$chanid]["recipient"] = $chanrecip;
      $u->channels[$chanid]["name"]      = $channame;
      $u->saveInCache();

      // clear the cached nicknames list for the given channel
      $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid."_".$chanid;
      $_SESSION[$nicklist_sid] = NULL;

      // reset the message id indicator
      // i.e. be ready to re-get all last posted messages
      $container =& $c->getContainerInstance();
      $from_id_sid = $c->prefix."from_id_".$c->getId()."_".$clientid."_".$chanid;
      $from_id     = $container->getLastId($chanrecip)-$c->max_msg;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;

      // show a join message
      $cmd =& pfcCommand::Factory("notice");
      $cmd->run($xml_reponse, $clientid, _pfc("%s joins %s",$u->nick, $channame), $sender, $chanrecip, $chanid, 1);
    }
    //$xml_reponse->addScript("alert('join: chan=".$channame."');");
    //    $xml_reponse->addScript("alert('join: u->nick=".$u->nick." chanid=".$chanid." channame=".addslashes($channame)."');");
    //    $xml_reponse->addScript("alert('join: fromidsid=".$from_id_sid."');");

    // return ok to the client
    // then the client will create a new tab
    $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ok', Array('".$chanid."','".addslashes($channame)."'));");
  }
  
}

?>